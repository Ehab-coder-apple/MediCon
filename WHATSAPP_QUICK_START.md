# WhatsApp Dual-Mode Quick Start Guide

## 🚀 Quick Implementation (4-6 hours)

### Prerequisites
- Laravel 11 application running
- Database access
- Composer installed

---

## Step 1: Database Migration (5 minutes)

```bash
# Run migration to add dual-mode columns
php artisan migrate

# Verify migration
php artisan migrate:status
```

**What it does**: Adds 8 new columns to `whatsapp_credentials` table for dual-mode support.

---

## Step 2: Add Routes (10 minutes)

**File**: `routes/web.php`

Add this code before the closing brace:

```php
// WhatsApp Dual-Mode Settings Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/settings/whatsapp', [TenantWhatsAppSettingsDualModeController::class, 'show'])
        ->name('tenant.whatsapp.show');
    Route::get('/settings/whatsapp/select-mode', [TenantWhatsAppSettingsDualModeController::class, 'selectMode'])
        ->name('tenant.whatsapp.select-mode');
    Route::post('/settings/whatsapp/mode', [TenantWhatsAppSettingsDualModeController::class, 'storeMode'])
        ->name('tenant.whatsapp.store-mode');
    Route::post('/settings/whatsapp/api-credentials', [TenantWhatsAppSettingsDualModeController::class, 'storeApiCredentials'])
        ->name('tenant.whatsapp.store-api');
    Route::post('/settings/whatsapp/business-free-credentials', [TenantWhatsAppSettingsDualModeController::class, 'storeBusinessFreeCredentials'])
        ->name('tenant.whatsapp.store-business-free');
    Route::post('/settings/whatsapp/switch-mode', [TenantWhatsAppSettingsDualModeController::class, 'switchMode'])
        ->name('tenant.whatsapp.switch-mode');
    Route::post('/settings/whatsapp/test', [TenantWhatsAppSettingsDualModeController::class, 'testConnection'])
        ->name('tenant.whatsapp.test');
    Route::post('/settings/whatsapp/enable', [TenantWhatsAppSettingsDualModeController::class, 'enable'])
        ->name('tenant.whatsapp.enable');
    Route::post('/settings/whatsapp/disable', [TenantWhatsAppSettingsDualModeController::class, 'disable'])
        ->name('tenant.whatsapp.disable');
});
```

**Don't forget**: Add import at top of file:
```php
use App\Http\Controllers\TenantWhatsAppSettingsDualModeController;
```

---

## Step 3: Create Blade Templates (60 minutes)

### Template 1: Main Settings Page
**File**: `resources/views/tenant-settings/whatsapp-dual-mode.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Current Configuration</h3>
                    
                    @if($credential)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Integration Mode</p>
                                <p class="text-lg font-bold">
                                    @if($credential->isApiMode())
                                        🔌 WhatsApp Business API
                                    @else
                                        📱 WhatsApp Business Free
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Status</p>
                                <p class="text-lg font-bold">
                                    @if($credential->is_enabled)
                                        <span class="text-green-600">✓ Enabled</span>
                                    @else
                                        <span class="text-red-600">✗ Disabled</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-600">No WhatsApp configuration found.</p>
                    @endif
                </div>
            </div>

            <!-- Mode Selection -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Select Integration Mode</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- API Mode -->
                        <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 cursor-pointer">
                            <h4 class="font-bold text-lg mb-2">🔌 WhatsApp Business API</h4>
                            <p class="text-gray-600 text-sm mb-4">
                                Automated messaging with full features. Best for growing pharmacies.
                            </p>
                            <ul class="text-sm text-gray-600 mb-4">
                                <li>✓ Automated sending</li>
                                <li>✓ Bulk messaging</li>
                                <li>✓ Message templates</li>
                                <li>✓ Delivery tracking</li>
                            </ul>
                            <form method="POST" action="{{ route('tenant.whatsapp.store-mode') }}">
                                @csrf
                                <input type="hidden" name="integration_type" value="api">
                                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                                    Choose API Mode
                                </button>
                            </form>
                        </div>

                        <!-- Business Free Mode -->
                        <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-green-500 cursor-pointer">
                            <h4 class="font-bold text-lg mb-2">📱 WhatsApp Business Free</h4>
                            <p class="text-gray-600 text-sm mb-4">
                                Manual messaging with no costs. Perfect for small pharmacies.
                            </p>
                            <ul class="text-sm text-gray-600 mb-4">
                                <li>✓ Completely free</li>
                                <li>✓ No API setup</li>
                                <li>✓ Simple to use</li>
                                <li>✓ Instant activation</li>
                            </ul>
                            <form method="POST" action="{{ route('tenant.whatsapp.store-mode') }}">
                                @csrf
                                <input type="hidden" name="integration_type" value="business_free">
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                    Choose Business Free
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            @if($credential)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Configuration</h3>
                        
                        @if($credential->isApiMode())
                            <!-- API Configuration Form -->
                            <form method="POST" action="{{ route('tenant.whatsapp.store-api') }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Business Account ID</label>
                                        <input type="text" name="business_account_id" value="{{ $credential->business_account_id }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number ID</label>
                                        <input type="text" name="phone_number_id" value="{{ $credential->phone_number_id }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="text" name="phone_number" value="{{ $credential->phone_number }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Access Token</label>
                                        <input type="password" name="access_token" placeholder="Enter your access token" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                                        <input type="password" name="webhook_secret" placeholder="Enter your webhook secret" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <button type="submit" class="bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                                        Save API Credentials
                                    </button>
                                </div>
                            </form>
                        @else
                            <!-- Business Free Configuration Form -->
                            <form method="POST" action="{{ route('tenant.whatsapp.store-business-free') }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Business Phone Number</label>
                                        <input type="tel" name="business_phone_number" value="{{ $credential->business_phone_number }}" placeholder="+1234567890" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Business Account Name</label>
                                        <input type="text" name="business_account_name" value="{{ $credential->business_account_name }}" placeholder="Your Pharmacy Name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <button type="submit" class="bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                        Save & Activate
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
```

### Template 2-4: Additional Templates
Create simplified versions for:
- `whatsapp-select-mode.blade.php` - Mode selection page
- `whatsapp-api-form.blade.php` - API form only
- `whatsapp-business-free-form.blade.php` - Business Free form only

---

## Step 4: Update WhatsAppController (30 minutes)

**File**: `app/Http/Controllers/WhatsAppController.php`

Update the `create()` method to use dual-mode service:

```php
use App\Services\WhatsAppDualModeService;

public function create(): View
{
    $tenantId = auth()->user()->tenant_id;
    $credential = WhatsAppCredential::forTenant($tenantId);
    
    if (!$credential) {
        return redirect()->route('tenant.whatsapp.show')
            ->with('error', 'Please configure WhatsApp first.');
    }

    $service = new WhatsAppDualModeService($credential);
    $modeInfo = $service->getModeInfo();

    $customers = Customer::where('tenant_id', $tenantId)
        ->whereNotNull('phone')
        ->orderBy('name')
        ->get();

    $templates = WhatsAppTemplate::forTenant($tenantId)
        ->active()
        ->approved()
        ->get();

    return view('whatsapp.create', compact('customers', 'templates', 'modeInfo'));
}
```

---

## Step 5: Test (30 minutes)

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Test routes
php artisan route:list | grep whatsapp

# Run tests
php artisan test
```

---

## Step 6: Deploy

```bash
# Commit changes
git add .
git commit -m "Add WhatsApp dual-mode support"

# Push to production
git push origin main

# Run migration on production
php artisan migrate --force
```

---

## 📚 Documentation Files

All documentation is in the project root:

1. **WHATSAPP_DUAL_MODE_ARCHITECTURE.md** - Technical architecture
2. **WHATSAPP_SETUP_GUIDE.md** - User setup instructions
3. **WHATSAPP_IMPLEMENTATION_CHECKLIST.md** - Detailed checklist
4. **WHATSAPP_IMPLEMENTATION_SUMMARY.md** - Complete summary
5. **WHATSAPP_QUICK_START.md** - This file

---

## ✅ Verification Checklist

- [ ] Database migration ran successfully
- [ ] Routes added to `routes/web.php`
- [ ] Blade templates created
- [ ] WhatsAppController updated
- [ ] Cache cleared
- [ ] Tests passing
- [ ] Settings page accessible
- [ ] Both modes selectable
- [ ] Credentials saveable
- [ ] Mode switching works

---

## 🆘 Troubleshooting

### Migration fails
```bash
# Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Routes not working
```bash
# Clear route cache
php artisan route:clear

# Verify routes
php artisan route:list | grep whatsapp
```

### Views not found
```bash
# Check view paths
php artisan view:clear

# Verify file locations
ls resources/views/tenant-settings/
```

---

## 📞 Support

For questions, refer to:
- `WHATSAPP_SETUP_GUIDE.md` - Setup instructions
- `WHATSAPP_DUAL_MODE_ARCHITECTURE.md` - Technical details
- Code comments in service and controller files

---

**Estimated Time**: 4-6 hours
**Difficulty**: Medium
**Status**: Ready to implement

