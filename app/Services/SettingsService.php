<?php

namespace App\Services;

use App\Contracts\SettingUnitEnum;
use App\Enums\SettingEnum;
use App\Events\SettingsEvent;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use function now;

class SettingsService
{
    private array $settings = [];
    private SettingsEvent $event;

    public function __construct()
    {
        $this->event = new SettingsEvent();
        $this->event->addUnits(SettingEnum::cases());
        event($this->event);

        Setting::whereIn('name', collect($this->getSettingUnits())->map(function (\UnitEnum $setting) {
            return $setting->name;
        }))->get()->map(function ($setting) {
            $this->settings[$setting->name] = $setting->value;
        });
    }

    /**
     * Get a setting value by enum key
     */
    public function get($unitEnum, mixed $default = null): mixed
    {
        if (!isset($this->settings[$unitEnum->name()])) {
            return $default;
        }

        $setting = $this->settings[$unitEnum->name()];

        return $setting;
    }

    /**
     * Set a setting value by enum key
     */
    public function set($key, mixed $value): Setting
    {
        $setting = Setting::updateOrCreate(
            ['name' => $key->name()],
            ['value' => $value]
        );

        return $setting;
    }

    /**
     * Get all settings as key-value pairs
     */
    public function getAll(): array
    {
        return Setting::pluck('value', 'name')->toArray();
    }

    /**
     * Delete a setting by enum key
     */
    public function delete($key): bool
    {
        return Setting::where('name', $key->name())->delete() > 0;
    }

    /**
     * Get setting with caching
     */
    public function getWithCache($key, mixed $default = null): mixed
    {
        $cacheKey = 'setting_' . $key->name();

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($key, $default) {
            return $this->get($key, $default);
        });
    }

    /**
     * Clear cache for a specific setting
     */
    public function clearCache($key): void
    {
        Cache::forget('setting_' . $key->name());
    }

    /**
     * Clear all settings cache
     */
    public function clearAllCache(): void
    {
        Setting::all()->each(function ($setting) {
            Cache::forget('setting_' . $setting->name);
        });
    }

    public function getSettingUnits(): array
    {
        return $this->event->getUnits();
    }
}
