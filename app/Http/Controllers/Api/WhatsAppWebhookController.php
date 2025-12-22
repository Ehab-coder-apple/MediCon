<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppCredential;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle WhatsApp webhook verification
     */
    public function verify(Request $request): string
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');
        $tenantId = $request->query('tenant_id');

        // Get tenant-specific credential if tenant_id is provided
        if ($tenantId) {
            $credential = WhatsAppCredential::forTenant($tenantId);
            $verifyToken = $credential?->webhook_secret ?? config('whatsapp.webhook.verify_token');
        } else {
            $verifyToken = config('whatsapp.webhook.verify_token');
        }

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully', ['tenant_id' => $tenantId]);
            return $challenge;
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
            'expected_token' => $verifyToken,
            'tenant_id' => $tenantId
        ]);

        abort(403, 'Forbidden');
    }

    /**
     * Handle WhatsApp webhook events
     */
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::info('WhatsApp webhook received', $data);

        try {
            if (isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            if ($change['field'] === 'messages') {
                                $this->processMessageUpdate($change['value']);
                            }
                        }
                    }
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Process message status updates
     */
    protected function processMessageUpdate(array $value): void
    {
        // Process message status updates
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->updateMessageStatus($status);
            }
        }

        // Process incoming messages (for future features like replies)
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->processIncomingMessage($message);
            }
        }
    }

    /**
     * Update message delivery status
     */
    protected function updateMessageStatus(array $status): void
    {
        $messageId = $status['id'] ?? null;
        $statusType = $status['status'] ?? null;
        $timestamp = $status['timestamp'] ?? null;

        if (!$messageId || !$statusType) {
            return;
        }

        $message = WhatsAppMessage::where('whatsapp_message_id', $messageId)->first();

        if (!$message) {
            Log::warning('WhatsApp message not found for status update', [
                'message_id' => $messageId,
                'status' => $statusType
            ]);
            return;
        }

        $updateData = ['status' => $statusType];

        switch ($statusType) {
            case 'sent':
                $updateData['sent_at'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : now();
                break;
            case 'read':
                $updateData['read_at'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : now();
                break;
            case 'failed':
                $updateData['error_message'] = $status['errors'][0]['title'] ?? 'Message delivery failed';
                break;
        }

        $message->update($updateData);

        Log::info('WhatsApp message status updated', [
            'message_id' => $messageId,
            'status' => $statusType,
            'customer_id' => $message->customer_id
        ]);
    }

    /**
     * Process incoming messages (for future features)
     */
    protected function processIncomingMessage(array $message): void
    {
        // This can be used for future features like:
        // - Auto-replies
        // - Customer service integration
        // - Order status inquiries

        Log::info('Incoming WhatsApp message received', [
            'from' => $message['from'] ?? null,
            'type' => $message['type'] ?? null,
            'timestamp' => $message['timestamp'] ?? null
        ]);
    }
}
