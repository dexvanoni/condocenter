<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (!Schema::hasTable('platform_settings')) {
            return $default;
        }

        $setting = static::query()->where('key', $key)->first();

        if (!$setting || $setting->value === null) {
            return $default;
        }

        if ($setting->is_encrypted) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $setting->value;
    }

    public static function setValue(string $key, mixed $value, bool $encrypt = false): void
    {
        if (!Schema::hasTable('platform_settings')) {
            return;
        }

        $stored = $encrypt && $value !== null && $value !== ''
            ? Crypt::encryptString((string) $value)
            : ($value === null ? null : (string) $value);

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'is_encrypted' => $encrypt]
        );
    }
}
