<?php

namespace App\Services;

use OpenAI\Client;
use Illuminate\Support\Facades\Log;

class OpenAIProductService
{
    private Client $client;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        $this->client = \OpenAI::client($apiKey);
    }

    /**
     * Get detailed product information from OpenAI
     */
    public function getProductInformation(string $productName): array
    {
        try {
            $prompt = $this->buildProductInfoPrompt($productName);

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            $content = $response['choices'][0]['message']['content'];
            
            // Parse JSON from response
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $content, $matches);
            if (!$jsonMatch) {
                throw new \Exception('Could not parse JSON from OpenAI response');
            }

            return json_decode($matches[0], true);
        } catch (\Exception $e) {
            Log::error('OpenAI Product Information Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the prompt for product information
     */
    private function buildProductInfoPrompt(string $productName): string
    {
        return <<<PROMPT
You are a pharmaceutical and medical information specialist.
Provide DETAILED SCIENTIFIC AND MEDICAL INFORMATION about: "$productName"

Focus on MEDICAL and SCIENTIFIC data ONLY - NOT stock, inventory, or pricing information.

Return the information as JSON with this exact structure:
{
  "name": "Product name",
  "activeIngredient": "Active ingredient(s)",
  "therapeuticClass": "Therapeutic classification",
  "mechanism": "How it works",
  "indications": ["Medical use 1", "Medical use 2"],
  "dosage": "Recommended dosage",
  "administration": "How to take it",
  "contraindications": ["When NOT to use 1", "When NOT to use 2"],
  "sideEffects": ["Side effect 1", "Side effect 2"],
  "interactions": ["Drug interaction 1", "Drug interaction 2"],
  "warnings": ["Clinical warning 1", "Clinical warning 2"],
  "pharmacokinetics": "ADME profile",
  "clinicalEfficacy": "Clinical trial results"
}
PROMPT;
    }
}

