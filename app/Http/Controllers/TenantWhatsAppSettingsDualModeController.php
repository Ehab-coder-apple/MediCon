<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppCredential;
use App\Services\WhatsAppDualModeService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Tenant WhatsApp Settings Controller - Dual Mode Support
 */
class TenantWhatsAppSettingsDualModeController extends Controller
{
    /**
     * Show WhatsApp settings with mode selection
     */
    public function show(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        $stats = [
            'is_enabled' => $credential?->is_enabled ?? false,
            'is_verified' => $credential?->is_verified ?? false,
            'integration_type' => $credential?->integration_type ?? 'api',
            'phone_number' => $credential?->phone_number ?? 'Not configured',
            'business_phone_number' => $credential?->business_phone_number ?? null,
            'business_account_name' => $credential?->business_account_name ?? null,
            'verified_at' => $credential?->verified_at,
            'last_tested_at' => $credential?->last_tested_at,
            'api_status' => $credential?->api_status ?? 'inactive',
            'business_free_status' => $credential?->business_free_status ?? 'inactive',
        ];

        return view('tenant-settings.whatsapp-dual-mode', compact('credential', 'stats'));
    }

    /**
     * Show mode selection page
     */
    public function selectMode(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);
        $currentMode = $credential?->integration_type ?? null;

        return view('tenant-settings.whatsapp-mode-selection', [
            'credential' => $credential,
            'currentMode' => $currentMode,
        ]);
    }

    /**
     * Store selected mode and show configuration form
     */
    public function storeMode(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'integration_type' => 'required|in:api,business_free',
        ]);

        $credential = WhatsAppCredential::forTenant($tenantId);

        if ($credential) {
            $credential->update(['integration_type' => $validated['integration_type']]);
        } else {
            WhatsAppCredential::create([
                'tenant_id' => $tenantId,
                'integration_type' => $validated['integration_type'],
                'is_enabled' => false,
                'is_verified' => false,
            ]);
        }

        ActivityLogService::log(
            action: 'updated',
            entityType: 'WhatsAppCredential',
            entityId: $credential?->id,
            entityName: 'WhatsApp Mode',
            description: "WhatsApp integration mode set to {$validated['integration_type']}",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'Integration mode selected. Please configure your credentials.');
    }

    /**
     * Show Business Free configuration form
     */
    public function configureBusinessFree(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        return view('tenant-settings.whatsapp-business-free-form', [
            'credential' => $credential,
        ]);
    }

    /**
     * Show API configuration form
     */
    public function configureApi(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        return view('tenant-settings.whatsapp-api-form', [
            'credential' => $credential,
        ]);
    }

    /**
     * Store API credentials
     */
    public function storeApiCredentials(Request $request): RedirectResponse
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
            $credential->update([
                ...$validated,
                'integration_type' => 'api',
                'api_status' => 'pending',
            ]);
        } else {
            $credential = WhatsAppCredential::create([
                'tenant_id' => $tenantId,
                ...$validated,
                'integration_type' => 'api',
                'is_enabled' => false,
                'is_verified' => false,
                'api_status' => 'pending',
            ]);
        }

        ActivityLogService::log(
            action: 'updated',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: $credential->phone_number,
            description: "WhatsApp API credentials configured",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'API credentials saved. Please verify them.');
    }

    /**
     * Store Business Free credentials
     */
    public function storeBusinessFreeCredentials(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'business_phone_number' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'business_account_name' => 'required|string|max:255',
        ]);

        $credential = WhatsAppCredential::forTenant($tenantId);

        if ($credential) {
            $credential->update([
                ...$validated,
                'integration_type' => 'business_free',
                'business_free_status' => 'active',
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        } else {
            $credential = WhatsAppCredential::create([
                'tenant_id' => $tenantId,
                ...$validated,
                'integration_type' => 'business_free',
                'is_enabled' => true,
                'is_verified' => true,
                'business_free_status' => 'active',
                'verified_at' => now(),
            ]);
        }

        ActivityLogService::log(
            action: 'updated',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: $credential->business_account_name,
            description: "WhatsApp Business Free credentials configured",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'Business Free configuration saved and activated!');
    }

    /**
     * Switch between modes
     */
    public function switchMode(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'integration_type' => 'required|in:api,business_free',
        ]);

        $credential = WhatsAppCredential::forTenant($tenantId);

        if (!$credential) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'No WhatsApp configuration found.');
        }

        $oldMode = $credential->integration_type;
        $credential->update(['integration_type' => $validated['integration_type']]);

        ActivityLogService::log(
            action: 'updated',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: 'WhatsApp Mode',
            description: "WhatsApp mode switched from {$oldMode} to {$validated['integration_type']}",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', "Switched to {$validated['integration_type']} mode.");
    }

    /**
     * Test connection
     */
    public function testConnection(): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $credential = WhatsAppCredential::forTenant($tenantId);

        if (!$credential) {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'No WhatsApp configuration found.');
        }

        $service = new WhatsAppDualModeService($credential);
        $result = $service->testConnection();

        if ($result['success']) {
            $credential->update([
                'last_tested_at' => now(),
                'test_result' => $result,
            ]);

            return redirect()->route('admin.whatsapp.show')
                ->with('success', 'Connection test successful!');
        } else {
            return redirect()->route('admin.whatsapp.show')
                ->with('error', 'Connection test failed: ' . ($result['error'] ?? 'Unknown error'));
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
            entityName: $credential->business_account_name ?? $credential->phone_number,
            description: "WhatsApp messaging enabled ({$credential->integration_type} mode)",
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
                ->with('error', 'No WhatsApp configuration found.');
        }

        $credential->update(['is_enabled' => false]);

        ActivityLogService::log(
            action: 'disabled',
            entityType: 'WhatsAppCredential',
            entityId: $credential->id,
            entityName: $credential->business_account_name ?? $credential->phone_number,
            description: "WhatsApp messaging disabled",
            category: 'system',
            severity: 'info'
        );

        return redirect()->route('admin.whatsapp.show')
            ->with('success', 'WhatsApp messaging disabled.');
    }
}

