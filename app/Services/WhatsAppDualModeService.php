<?php

namespace App\Services;

use App\Models\WhatsAppCredential;
use App\Models\WhatsAppMessage;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Dual-Mode Service
 * 
 * Handles both WhatsApp Business API and Business Free modes
 */
class WhatsAppDualModeService
{
    private WhatsAppCredential $credential;
    private string $mode;
    private WhatsAppService $apiService;

    public function __construct(WhatsAppCredential $credential)
    {
        $this->credential = $credential;
        $this->mode = $credential->integration_type ?? 'api';
        $this->apiService = new WhatsAppService($credential);
    }

    /**
     * Send message using appropriate mode
     */
    public function sendMessage(string $to, string $message, array $options = []): array
    {
        try {
            if ($this->mode === 'business_free') {
                return $this->sendViaBusinessFree($to, $message, $options);
            } else {
                return $this->sendViaAPI($to, $message, $options);
            }
        } catch (Exception $e) {
            Log::error('WhatsApp message sending failed', [
                'mode' => $this->mode,
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            if ($this->mode === 'api') {
                $this->credential->markApiError($e->getMessage());
            } else {
                $this->credential->markBusinessFreeError($e->getMessage());
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mode' => $this->mode,
            ];
        }
    }

    /**
     * Send via WhatsApp Business API
     */
    private function sendViaAPI(string $to, string $message, array $options = []): array
    {
        if (!$this->credential->isApiConfigured()) {
            throw new Exception('API credentials not configured');
        }

        if (!$this->credential->is_enabled || !$this->credential->is_verified) {
            throw new Exception('API credentials not verified or enabled');
        }

        // Use existing WhatsAppService for API
        $result = $this->apiService->sendTextMessage($to, $message, $options);

        if ($result['success']) {
            $this->credential->markApiActive();
        } else {
            $this->credential->markApiError($result['error'] ?? 'Unknown error');
        }

        return array_merge($result, ['mode' => 'api']);
    }

    /**
     * Send via WhatsApp Business Free (Manual)
     */
    private function sendViaBusinessFree(string $to, string $message, array $options = []): array
    {
        if (!$this->credential->isBusinessFreeConfigured()) {
            throw new Exception('Business Free credentials not configured');
        }

        // Generate WhatsApp link for manual sending
        $whatsappLink = $this->credential->getWhatsAppLink($to, $message);

        // Log the message as pending
        $this->logBusinessFreeMessage($to, $message, $whatsappLink, $options);

        $this->credential->markBusinessFreeActive();

        return [
            'success' => true,
            'mode' => 'business_free',
            'message' => 'Message link generated. Please click the link to send manually.',
            'whatsapp_link' => $whatsappLink,
            'phone_number' => $to,
            'message_text' => $message,
            'instructions' => 'Click the link below to open WhatsApp and send the message manually.',
        ];
    }

    /**
     * Log Business Free message for tracking
     */
    private function logBusinessFreeMessage(
        string $to,
        string $message,
        string $whatsappLink,
        array $options = []
    ): void {
        try {
            WhatsAppMessage::create([
                'tenant_id' => $this->credential->tenant_id,
                'user_id' => $options['user_id'] ?? auth()->id(),
                'customer_id' => $options['customer_id'] ?? null,
                'recipient_phone' => $to,
                'message_type' => 'text',
                'message_content' => $message,
                'status' => 'pending', // Pending manual sending
                'metadata' => [
                    'mode' => 'business_free',
                    'whatsapp_link' => $whatsappLink,
                    'manual_send_required' => true,
                ],
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to log Business Free message', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send bulk messages
     */
    public function sendBulkMessages(array $recipients, string $message, array $options = []): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $result = $this->sendMessage($recipient, $message, $options);
            $results[] = array_merge($result, ['recipient' => $recipient]);

            // Add delay to respect rate limits
            usleep(100000); // 100ms
        }

        return [
            'success' => true,
            'mode' => $this->mode,
            'total_recipients' => count($recipients),
            'results' => $results,
        ];
    }

    /**
     * Get mode information
     */
    public function getModeInfo(): array
    {
        return [
            'mode' => $this->mode,
            'is_api' => $this->mode === 'api',
            'is_business_free' => $this->mode === 'business_free',
            'is_configured' => $this->isConfigured(),
            'is_enabled' => $this->credential->is_enabled,
            'is_verified' => $this->credential->is_verified,
            'can_send_automated' => $this->credential->canSendAutomated(),
            'status' => $this->getStatus(),
        ];
    }

    /**
     * Check if mode is properly configured
     */
    public function isConfigured(): bool
    {
        if ($this->mode === 'api') {
            return $this->credential->isApiConfigured();
        } else {
            return $this->credential->isBusinessFreeConfigured();
        }
    }

    /**
     * Get current status
     */
    public function getStatus(): string
    {
        if ($this->mode === 'api') {
            return $this->credential->api_status ?? 'inactive';
        } else {
            return $this->credential->business_free_status ?? 'inactive';
        }
    }

    /**
     * Get error message
     */
    public function getErrorMessage(): ?string
    {
        if ($this->mode === 'api') {
            return $this->credential->api_error_message;
        } else {
            return $this->credential->business_free_error_message;
        }
    }

    /**
     * Switch mode
     */
    public function switchMode(string $newMode): bool
    {
        if (!in_array($newMode, ['api', 'business_free'])) {
            throw new Exception('Invalid mode: ' . $newMode);
        }

        $this->credential->update(['integration_type' => $newMode]);
        $this->mode = $newMode;

        Log::info('WhatsApp mode switched', [
            'tenant_id' => $this->credential->tenant_id,
            'old_mode' => $this->mode,
            'new_mode' => $newMode,
        ]);

        return true;
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        try {
            if ($this->mode === 'api') {
                return $this->apiService->testConnection();
            } else {
                // Business Free doesn't need testing
                return [
                    'success' => true,
                    'message' => 'Business Free mode is ready to use',
                    'mode' => 'business_free',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mode' => $this->mode,
            ];
        }
    }
}

