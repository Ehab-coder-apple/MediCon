<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Traits\HasRoleBasedRouting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
class SystemSettingsController extends Controller
{
    use HasRoleBasedRouting;

    /**
     * Display the system settings dashboard
     */
    public function index(): View
    {
        $this->authorize('access-admin-dashboard');

        // Get all settings grouped by category
        $settings = $this->getSettingsGrouped();

        // Get available currencies
        $currencies = $this->getAvailableCurrencies();

        // System information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        // Storage information
        $storageInfo = [
            'total_space' => $this->formatBytes(disk_total_space(storage_path())),
            'free_space' => $this->formatBytes(disk_free_space(storage_path())),
            'used_space' => $this->formatBytes(disk_total_space(storage_path()) - disk_free_space(storage_path())),
        ];

        return view('settings.index', compact('settings', 'systemInfo', 'storageInfo', 'currencies'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear settings cache
        Cache::forget('system_settings');

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }

    /**
     * Backup system data
     */
    public function backup(): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        try {
            // Run database backup command
            Artisan::call('backup:run', ['--only-db' => true]);
            
            return redirect()->back()->with('success', 'Database backup created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache(): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->back()->with('success', 'Application cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cache clear failed: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application
     */
    public function optimize(): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return redirect()->back()->with('success', 'Application optimized successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Get available currencies
     */
    public function getAvailableCurrencies(): array
    {
        return [
            '$' => 'USD - Dollar ($)',
            'IQD' => 'IQD - Iraqi Dinar (IQD)',
            'JD' => 'JD - Jordanian Dinar (JD)',
            'EGP' => 'EGP - Egyptian Pound (EGP)',
            'SR' => 'SR - Saudi Riyal (SR)',
            'AED' => 'AED - United Arab Emirates Dirham (AED)',
        ];
    }

    /**
     * Get settings grouped by category
     */
    private function getSettingsGrouped(): array
    {
        $defaultSettings = [
            'general' => [
                'app_name' => config('app.name', 'MediCon'),
                'pharmacy_name' => 'Your Pharmacy Name',
                'pharmacy_address' => 'Your Pharmacy Address',
                'pharmacy_phone' => '+1-555-0123',
                'pharmacy_email' => 'info@pharmacy.com',
                'pharmacy_license' => 'LICENSE-NUMBER',
                'timezone' => config('app.timezone', 'UTC'),
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'tax_rate' => '0.08',
            ],
            'inventory' => [
                'low_stock_alert_enabled' => 'true',
                'expiry_alert_days' => '30',
                'auto_reorder_enabled' => 'false',
                'default_alert_quantity' => '10',
                'batch_tracking_enabled' => 'true',
            ],
            'sales' => [
                'invoice_prefix' => 'INV',
                'receipt_prefix' => 'RCP',
                'allow_negative_stock' => 'false',
                'require_customer_info' => 'false',
                'auto_print_receipt' => 'true',
                'discount_enabled' => 'true',
                'max_discount_percentage' => '20',
            ],

            'notifications' => [
                'email_notifications_enabled' => 'true',
                'sms_notifications_enabled' => 'false',
                'low_stock_notifications' => 'true',
                'expiry_notifications' => 'true',
                'daily_reports_enabled' => 'true',
                'notification_email' => 'admin@pharmacy.com',
            ],
            'security' => [
                'session_timeout' => '120',
                'password_min_length' => '8',
                'require_password_change' => 'false',
                'max_login_attempts' => '5',
                'lockout_duration' => '15',
                'two_factor_enabled' => 'false',
            ],
        ];

        // Get existing settings from database
        $existingSettings = SystemSetting::pluck('value', 'key')->toArray();

        // Merge with defaults
        $settings = [];
        foreach ($defaultSettings as $category => $categorySettings) {
            $settings[$category] = [];
            foreach ($categorySettings as $key => $defaultValue) {
                $settings[$category][$key] = $existingSettings[$key] ?? $defaultValue;
            }
        }

        return $settings;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Test email configuration
     */
    public function testEmail(): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        try {
            $testEmail = auth()->user()->email;
            
            // Send test email
            \Mail::raw('This is a test email from MediCon system.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('MediCon - Test Email');
            });
            
            return redirect()->back()->with('success', 'Test email sent successfully to ' . $testEmail);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Email test failed: ' . $e->getMessage());
        }
    }

    /**
     * Get system logs
     */
    public function logs(): View
    {
        $this->authorize('access-admin-dashboard');

        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logLines = array_reverse(explode("\n", $logContent));
            
            // Get last 100 log entries
            $logs = array_slice(array_filter($logLines), 0, 100);
        }

        return view('settings.logs', compact('logs'));
    }

    /**
     * Clear system logs
     */
    public function clearLogs(): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }
            
            return redirect()->back()->with('success', 'System logs cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear logs: ' . $e->getMessage());
        }
    }
}
