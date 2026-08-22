<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    /**
     * Cache all settings forever.
     */
    public static function getAllSettings(): array
    {
        return Cache::rememberForever('settings_cached', function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Retrieve setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getAllSettings();
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Update/Set setting value.
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
        self::clearCache();
    }

    /**
     * Clear cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget('settings_cached');
    }
}
