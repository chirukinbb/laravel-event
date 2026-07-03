<?php

namespace Modules\Events\Repositories;

use App\Models\Country;
use App\Models\Point;

class GeoRepository
{
    public string $iso;
    public string $point_id;
    public int $country_id;
    public float $lat;
    public float $lng;

    public function __construct(protected string $address)
    {
    }

    public function geocoding()
    {
        $response = \Http::withHeaders(['Referer' => route('home')])->get(
            sprintf('https://api.tomtom.com/search/2/geocode/%s.json', $this->address),
            ['key' => env('TOM_TOM_GEOCODING_API_KEY')]
        );
        $address = json_decode($response->body())->results[0]->address;

        $this->lat = json_decode($response->body())->results[0]->position->lat;
        $this->lng = json_decode($response->body())->results[0]->position->lon;

        $this->setCountry($address->countryCode, $address->country);
        $this->setPoint($address->municipalitySubdivision);
    }

    protected function setCountry(string $iso, string $name)
    {
        $countryModel = Country::whereIso($iso)->first();

        if (is_null($countryModel)) {
            $countryModel = Country::getModel();

            $countryModel->iso = $iso;
            $countryModel->name = $name;

            $countryModel->save();
        }

        $this->country_id = $countryModel->id;
    }

    protected function setPoint(string $name)
    {
        $pointModel = Point::whereName($name)->first();

        if (is_null($pointModel)) {
            $pointModel = Point::getModel();

            $pointModel->name = $name;

            $pointModel->save();
        }

        $this->point_id = $pointModel->id;
    }
}
