<?php

namespace Modules\Events\Repositories;

use App\Models\Country;
use App\Models\Point;
use Illuminate\Support\Facades\Http;

class GeoRepository
{
    public string $iso;
    public string $point_id;
    public int $country_id;
    public float $lat;
    public float $lng;

    // Сюда будет записываться полученный адрес при реверс-геокодинге
    public ?string $address = null;

    public function __construct(?string $address = null)
    {
        $this->address = $address;
    }

    /**
     * Прямое геокодирование (Адрес -> Координаты)
     */
    public function geocoding(): void
    {
        if (!$this->address) {
            return;
        }

        $response = Http::withHeaders(['Referer' => route('dashboard')])->get(
            sprintf('https://api.tomtom.com/search/2/geocode/%s.json', urlencode($this->address)),
            ['key' => env('TOM_TOM_GEOCODING_API_KEY')]
        );

        if ($response->successful() && isset($response->json()['results'][0])) {
            $result = $response->json()['results'][0];

            $this->lat = (float)$result['position']['lat'];
            $this->lng = (float)$result['position']['lon'];
        }
    }

    /**
     * Статический метод для обратного геокодирования (Координаты -> Адрес)
     */
    public static function reverseGeocoding(float $lat, float $lng): ?self
    {
        $response = Http::withHeaders(['Referer' => route('dashboard')])->get(
            sprintf('https://api.tomtom.com/search/2/reverseGeocode/%s,%s.json', $lat, $lng),
            [
                'key' => env('TOM_TOM_GEOCODING_API_KEY'),
                'lang' => 'ru-RU' // Можно убрать или поменять локаль при необходимости
            ]
        );

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['addresses'][0])) {
                $addressData = $data['addresses'][0];
                $freeformAddress = $addressData['address']['freeformAddress'] ?? null;

                // Создаем инстанс репозитория и наполняем его данными
                $repo = new self($freeformAddress);
                $repo->lat = $lat;
                $repo->lng = $lng;
                $repo->iso = $addressData['address']['countryCode'] ?? '';

                return $repo;
            }
        }

        return null;
    }

    public function getPosition(): array
    {
        return [$this->lat, $this->lng];
    }
}