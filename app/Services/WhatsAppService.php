<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppCredential;
use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Exception;

class WhatsAppService
{
    protected $baseUrl;
    protected $accessToken;
    protected $phoneNumberId;
    protected $apiVersion;
    protected $enabled;
    protected $credential;
    protected $tenantId;

    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId ?? (Auth::check() ? Auth::user()->tenant_id : null);
        $this->baseUrl = config('whatsapp.api.base_url');
        $this->apiVersion = config('whatsapp.api.version');

        // Try to load tenant-specific credentials first
        if ($this->tenantId) {
            $this->credential = WhatsAppCredential::enabledForTenant($this->tenantId);
            if ($this->credential) {
                $this->accessToken = $this->credential->access_token;
                $this->phoneNumberId = $this->credential->phone_number_id;
                $this->enabled = true;
                return;
            }
        }

        // Fall back to environment configuration
        $this->enabled = config('whatsapp.enabled', false);
        $this->accessToken = config('whatsapp.credentials.access_token');
        $this->phoneNumberId = config('whatsapp.credentials.phone_number_id');
    }

    /**
     * Check if WhatsApp service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->accessToken) && !empty($this->phoneNumberId);
    }

    /**
     * Check if we're in test mode (for demo purposes)
     */
    public function isTestMode(): bool
    {
        return config('whatsapp.test_mode', true);
    }

    /**
     * Send a text message to a single recipient
     */
    public function sendTextMessage(string $to, string $message, array $options = []): array
    {
        // Test mode - simulate successful sending
        if ($this->isTestMode()) {
            return $this->simulateMessageSending($to, $message, 'text', $options);
        }

        if (!$this->isEnabled()) {
            throw new Exception('WhatsApp service is not enabled or configured properly');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return $this->sendMessage($payload, $options);
    }

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = [], array $options = []): array
    {
        // Test mode - simulate successful sending
        if ($this->isTestMode()) {
            return $this->simulateMessageSending($to, "Template: $templateName", 'template', $options);
        }

        if (!$this->isEnabled()) {
            throw new Exception('WhatsApp service is not enabled or configured properly');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $options['language'] ?? 'en'
                ]
            ]
        ];

        if (!empty($parameters)) {
            $payload['template']['components'] = $this->formatTemplateParameters($parameters);
        }

        return $this->sendMessage($payload, $options);
    }

    /**
     * Send media message (image, document, video, audio)
     */
    public function sendMediaMessage(string $to, string $mediaUrl, string $mediaType, array $options = []): array
    {
        if (!$this->isEnabled()) {
            throw new Exception('WhatsApp service is not enabled or configured properly');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => $mediaType,
            $mediaType => [
                'link' => $mediaUrl
            ]
        ];

        // Add caption if provided
        if (isset($options['caption']) && in_array($mediaType, ['image', 'video', 'document'])) {
            $payload[$mediaType]['caption'] = $options['caption'];
        }

        return $this->sendMessage($payload, $options);
    }

    /**
     * Send bulk messages to multiple recipients
     */
    public function sendBulkMessages(array $recipients, string $message, array $options = []): array
    {
        // Test mode - simulate bulk sending
        if ($this->isTestMode()) {
            return $this->simulateBulkSending($recipients, $message, 'text', $options);
        }

        $results = [];
        $bulkMessageId = uniqid('bulk_');

        foreach ($recipients as $recipient) {
            try {
                $result = $this->sendTextMessage($recipient, $message, array_merge($options, [
                    'bulk_message_id' => $bulkMessageId,
                    'is_bulk_message' => true
                ]));
                $results[] = $result;

                // Add delay between messages to respect rate limits
                usleep(100000); // 100ms delay

            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'bulk_message_id' => $bulkMessageId,
            'total_sent' => count($recipients),
            'results' => $results
        ];
    }

    /**
     * Send bulk template messages
     */
    public function sendBulkTemplateMessages(array $recipients, string $templateName, array $parameters = [], array $options = []): array
    {
        $results = [];
        $bulkMessageId = uniqid('bulk_template_');

        foreach ($recipients as $recipient) {
            try {
                $result = $this->sendTemplateMessage($recipient, $templateName, $parameters, array_merge($options, [
                    'bulk_message_id' => $bulkMessageId,
                    'is_bulk_message' => true
                ]));
                $results[] = $result;
                
                // Add delay between messages
                usleep(100000); // 100ms delay
                
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'bulk_message_id' => $bulkMessageId,
            'total_sent' => count($recipients),
            'results' => $results
        ];
    }

    /**
     * Get message status from WhatsApp API
     */
    public function getMessageStatus(string $messageId): array
    {
        if (!$this->isEnabled()) {
            throw new Exception('WhatsApp service is not enabled');
        }

        $url = "{$this->baseUrl}/{$this->apiVersion}/{$messageId}";

        $response = Http::withToken($this->accessToken)
            ->timeout(config('whatsapp.api.timeout', 30))
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Failed to get message status: ' . $response->body());
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Check if it's a valid international format (10-15 digits)
        return preg_match('/^[1-9]\d{9,14}$/', $cleaned);
    }

    /**
     * Format phone number for WhatsApp API
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If number doesn't start with country code, assume it's local and add default
        if (strlen($cleaned) === 10 && !str_starts_with($cleaned, '1')) {
            $cleaned = '1' . $cleaned; // Add US country code as default
        }
        
        return $cleaned;
    }

    /**
     * Format template parameters for WhatsApp API
     */
    protected function formatTemplateParameters(array $parameters): array
    {
        $components = [];
        
        if (!empty($parameters)) {
            $bodyParameters = [];
            foreach ($parameters as $param) {
                $bodyParameters[] = [
                    'type' => 'text',
                    'text' => (string) $param
                ];
            }
            
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters
            ];
        }
        
        return $components;
    }

    /**
     * Send message to WhatsApp API
     */
    protected function sendMessage(array $payload, array $options = []): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(config('whatsapp.api.timeout', 30))
                ->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['messages'][0]['id'])) {
                $result = [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'],
                    'recipient' => $payload['to'],
                    'response' => $responseData
                ];

                // Log the message if enabled
                if (config('whatsapp.logging.enabled')) {
                    Log::channel(config('whatsapp.logging.channel', 'default'))
                        ->info('WhatsApp message sent', $result);
                }

                return $result;
            } else {
                throw new Exception('Failed to send message: ' . $response->body());
            }

        } catch (Exception $e) {
            $error = [
                'success' => false,
                'recipient' => $payload['to'],
                'error' => $e->getMessage(),
                'payload' => $payload
            ];

            // Log the error
            if (config('whatsapp.logging.enabled')) {
                Log::channel(config('whatsapp.logging.channel', 'default'))
                    ->error('WhatsApp message failed', $error);
            }

            throw $e;
        }
    }

    /**
     * Check rate limits
     */
    protected function checkRateLimit(): bool
    {
        $key = 'whatsapp_rate_limit_' . $this->phoneNumberId;
        $currentCount = Cache::get($key, 0);
        $limit = config('whatsapp.rate_limits.messages_per_minute', 100);

        if ($currentCount >= $limit) {
            return false;
        }

        Cache::put($key, $currentCount + 1, 60); // 1 minute TTL
        return true;
    }

    /**
     * Simulate message sending for test mode
     */
    protected function simulateMessageSending(string $to, string $message, string $type, array $options = []): array
    {
        // Generate a fake message ID
        $messageId = 'test_msg_' . uniqid();

        // Log the simulated message
        Log::info('WhatsApp Test Mode - Message Simulated', [
            'to' => $to,
            'message' => $message,
            'type' => $type,
            'message_id' => $messageId
        ]);

        return [
            'success' => true,
            'message_id' => $messageId,
            'recipient' => $to,
            'status' => 'sent',
            'test_mode' => true
        ];
    }

    /**
     * Simulate bulk message sending for test mode
     */
    protected function simulateBulkSending(array $recipients, string $message, string $type, array $options = []): array
    {
        $bulkMessageId = 'test_bulk_' . uniqid();
        $results = [];

        foreach ($recipients as $recipient) {
            $results[] = $this->simulateMessageSending($recipient, $message, $type, $options);
        }

        Log::info('WhatsApp Test Mode - Bulk Message Simulated', [
            'bulk_message_id' => $bulkMessageId,
            'recipients_count' => count($recipients),
            'message' => $message,
            'type' => $type
        ]);

        return [
            'bulk_message_id' => $bulkMessageId,
            'total_sent' => count($recipients),
            'results' => $results
        ];
    }
}
