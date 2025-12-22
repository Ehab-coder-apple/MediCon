<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppCredential;
use App\Services\WhatsAppService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TenantWhatsAppSettingsController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->middleware('auth');
    }

    /**
     * Show WhatsApp settings form
     */
    public function show(): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (!$tenantId) {
            abort(403, 'No tenant found for this user.');
        }

        $credential = WhatsAppCredential::forTenant($tenantId);

        $stats = [
            'is_enabled' => $credential?->is_enabled ?? false,
            'is_verified' => $credential?->is_verified ?? false,
            'phone_number' => $credential?->phone_number ?? 'Not configured',
            'verified_at' => $credential?->verified_at,
            'last_tested_at' => $credential?->last_tested_at,
            'test_result' => $credential?->test_result,
        ];

        return view('tenant-settings.whatsapp', compact('credential', 'stats'));
    }

    /**
     * Store WhatsApp credentials
     */
    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'business_account_id' => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'access_token' => 'required|string',
            'webhook_secret' => 'required|string',
        ]);

        $credential = WhatsAppCredential::forTenant($tenantId);

        if ($credential) {
            // Update existing
            $oldData = $credential->only(['business_account_id', 'phone_number_id', 'phone_number']);
            $credential->update($validated);
            $credential->is_verified = false; // Reset verification on update
            $credential->save();

            // Log the update
            ActivityLogService::log(
                action: 'updated',
                entityType: 'WhatsAppCredential',
                entityId: $credential->id,
                entityName: $credential->phone_number,
                description: "WhatsApp credentials updated for phone number {$credential->phone_number}",
                changes: ['old' => $oldData, 'new' => $validated],
                category: 'system',
                severity: 'warning'
            );

            return redirect()->route('admin.whatsapp.show')
                ->with('success', 'WhatsApp credentials updated. Please verify them.');
        } else {
            // Create new
            $credential = WhatsAppCredential::create([
                'tenant_id' => $tenantId,
                ...$validated,
                'is_enabled' => false,
                'is_verified' => false,
            ]);

            // Log the creation
            ActivityLogService::log(
                action: 'created',
                entityType: 'WhatsAppCredential',
                entityId: $credential->id,
                entityName: $credential->phone_number,
                description: "WhatsApp credentials configured for phone number {$credential->phone_number}",
                category: 'system',
                severity: 'info'
            );

            return redirect()->route('admin.whatsapp.show')
                ->with('success', 'WhatsApp credentials saved. Please verify them.');
        }
    }

    /**
     * Test WhatsApp credentials
     */
    public function test(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        if (!$credential || !$credential->isComplete()) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Please configure credentials first.');
        }

        try {
            // Test the credentials by making a simple API call
            $testResult = $this->testWhatsAppCredentials($credential);

            if ($testResult['success']) {
                $credential->update([
                    'is_verified' => true,
                    'verified_at' => now(),
                    'last_tested_at' => now(),
                    'test_result' => $testResult,
                ]);

                // Log successful verification
                ActivityLogService::log(
                    action: 'verified',
                    entityType: 'WhatsAppCredential',
                    entityId: $credential->id,
                    entityName: $credential->phone_number,
                    description: "WhatsApp credentials verified successfully for {$credential->phone_number}",
                    category: 'system',
                    severity: 'info'
                );

                return redirect()->route('admin.whatsapp.show')
                    ->with('success', 'WhatsApp credentials verified successfully!');
            } else {
                $credential->update([
                    'last_tested_at' => now(),
                    'test_result' => $testResult,
                ]);

                // Log failed verification
                ActivityLogService::log(
                    action: 'failed_verification',
                    entityType: 'WhatsAppCredential',
                    entityId: $credential->id,
                    entityName: $credential->phone_number,
                    description: "WhatsApp credentials verification failed: {$testResult['error']}",
                    category: 'system',
                    severity: 'error'
                );

                return redirect()->route('admin.whatsapp.show')
                    ->with('error', 'Verification failed: ' . $testResult['error']);
            }
        } catch (\Exception $e) {
            $credential->update([
                'last_tested_at' => now(),
                'test_result' => ['success' => false, 'error' => $e->getMessage()],
            ]);

            // Log the error
            ActivityLogService::log(
                action: 'error',
                entityType: 'WhatsAppCredential',
                entityId: $credential->id,
                entityName: $credential->phone_number,
                description: "WhatsApp credentials test error: {$e->getMessage()}",
                category: 'system',
                severity: 'error'
            );

            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Error testing credentials: ' . $e->getMessage());
        }
    }

    /**
     * Enable WhatsApp
     */
    public function enable(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        if (!$credential) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Please configure credentials first.');
        }

        if (!$credential->is_verified) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Please verify credentials first.');
        }

        $credential->update(['is_enabled' => true]);

        ActivityLogService::log(
            action: 'enabled',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: $credential->phone_number,
            description: "WhatsApp messaging enabled for {$credential->phone_number}",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'WhatsApp messaging enabled!');
    }

    /**
     * Disable WhatsApp
     */
    public function disable(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        if (!$credential) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Credentials not found.');
        }

        $credential->update(['is_enabled' => false]);

        ActivityLogService::log(
            action: 'disabled',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: $credential->phone_number,
            description: "WhatsApp messaging disabled for {$credential->phone_number}",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'WhatsApp messaging disabled.');
    }

    /**
     * Test WhatsApp credentials by calling the API
     */
    private function testWhatsAppCredentials(WhatsAppCredential $credential): array
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(
                "https://graph.facebook.com/v16.0/{$credential->phone_number_id}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$credential->access_token}",
                    ],
                ]
            );

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'message' => 'Credentials verified',
                    'phone_number' => $data['display_phone_number'] ?? $credential->phone_number,
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid response from WhatsApp API',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
