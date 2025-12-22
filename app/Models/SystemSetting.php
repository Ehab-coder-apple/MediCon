<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
        'category'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $description = null, string $type = 'text', string $category = 'general'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type,
                'category' => $category
            ]
        );

        // Clear cache
        Cache::forget('system_settings');

        return $setting;
    }

    /**
     * Get all settings grouped by category
     */
    public static function getGrouped(): array
    {
        $settings = self::all()->groupBy('category');
        
        return $settings->map(function ($categorySettings) {
            return $categorySettings->pluck('value', 'key')->toArray();
        })->toArray();
    }

    /**
     * Check if a boolean setting is enabled
     */
    public static function isEnabled(string $key): bool
    {
        $value = self::get($key, 'false');
        return in_array(strtolower($value), ['true', '1', 'yes', 'on', 'enabled']);
    }

    /**
     * Get numeric setting value
     */
    public static function getNumeric(string $key, $default = 0): float
    {
        $value = self::get($key, $default);
        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Get array setting value (comma-separated)
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = self::get($key);
        
        if (empty($value)) {
            return $default;
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * Boot method to clear cache when settings are updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('system_settings');
        });

        static::deleted(function () {
            Cache::forget('system_settings');
        });
    }
}
