<?php

namespace App\Services;

use App\Models\Setting;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeocodingService
{
    private const CAMBODIA_BBOX = '102.3,10.4,107.6,14.7';

    public function geocode(string $query): ?array
    {
        $query = trim(preg_replace('/\s+/u', ' ', $query));

        if ($query === '') {
            return null;
        }

        if ($coordinates = $this->parseCoordinates($query)) {
            return $coordinates;
        }

        [$storeLat, $storeLng] = $this->storeCoordinates();

        foreach ($this->buildQueryVariants($query) as $variant) {
            if ($result = $this->nominatimSearch($variant, $storeLat, $storeLng)) {
                return $result;
            }
        }

        foreach ($this->buildQueryVariants($query) as $variant) {
            if ($result = $this->photonSearch($variant, $storeLat, $storeLng)) {
                return $result;
            }
        }

        return null;
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $client = $this->httpClient([
                'User-Agent' => $this->nominatimUserAgent(),
                'Accept' => 'application/json',
            ]);

            if ($client === null) {
                return null;
            }

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $payload = $response->json();

                if (is_array($payload)) {
                    $formatted = $this->formatNominatimAddress($payload);

                    if ($formatted !== null && $formatted !== '') {
                        return $formatted;
                    }
                }
            }
        } catch (\Throwable) {
            // Fall through to Photon reverse geocoding.
        }

        try {
            $client = $this->httpClient([
                'Accept' => 'application/json',
            ]);

            if ($client === null) {
                return null;
            }

            $photonResponse = $client->get('https://photon.komoot.io/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'lang' => 'en',
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $photonResponse->successful()) {
            return null;
        }

        $feature = $photonResponse->json('features.0');

        if (! is_array($feature)) {
            return null;
        }

        return $this->formatPhotonAddress($feature['properties'] ?? []);
    }

    private function httpClient(array $headers = []): ?PendingRequest
    {
        try {
            $request = Http::timeout(12)->withOptions([
                'verify' => $this->resolveSslVerify(),
            ]);

            if ($headers !== []) {
                $request = $request->withHeaders($headers);
            }

            return $request;
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveSslVerify(): bool|string
    {
        $configured = config('services.http.verify_ssl');

        if ($configured !== null && $configured !== '') {
            if (in_array(strtolower((string) $configured), ['false', '0', 'no', 'off'], true)) {
                return false;
            }

            if (is_string($configured) && is_file($configured)) {
                return $configured;
            }
        }

        try {
            return CaBundle::getSystemCaRootBundlePath();
        } catch (\Throwable) {
            return app()->environment('local', 'testing') ? false : true;
        }
    }

    private function parseCoordinates(string $query): ?array
    {
        if (! preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*[,;\s]\s*(-?\d+(?:\.\d+)?)\s*$/u', $query, $matches)) {
            return null;
        }

        $lat = (float) $matches[1];
        $lng = (float) $matches[2];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
            'display_name' => $query,
        ];
    }

    private function buildQueryVariants(string $query): array
    {
        $variants = [$query];
        $lower = mb_strtolower($query);

        if (! str_contains($lower, 'phnom penh') && ! str_contains($lower, 'cambodia')) {
            $variants[] = $query.', Phnom Penh, Cambodia';
        } elseif (! str_contains($lower, 'cambodia')) {
            $variants[] = $query.', Cambodia';
        }

        return array_values(array_unique($variants));
    }

    private function storeCoordinates(): array
    {
        return [
            (float) Setting::get('map_lat', 11.5564),
            (float) Setting::get('map_long', 104.9282),
        ];
    }

    private function nominatimUserAgent(): string
    {
        $email = (string) config('mail.from.address', 'dev@cambomart.com');
        $url = (string) config('app.url', 'http://localhost');

        return config('app.name', 'CamboMart').'/1.0 ('.$url.'; '.$email.')';
    }

    private function nominatimSearch(string $query, float $storeLat, float $storeLng): ?array
    {
        $viewbox = implode(',', [
            $storeLng - 0.8,
            $storeLat + 0.8,
            $storeLng + 0.8,
            $storeLat - 0.8,
        ]);

        try {
            $client = $this->httpClient([
                'User-Agent' => $this->nominatimUserAgent(),
                'Accept' => 'application/json',
            ]);

            if ($client === null) {
                return null;
            }

            $response = $client->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'limit' => 1,
                'q' => $query,
                'countrycodes' => 'kh',
                'viewbox' => $viewbox,
                'bounded' => 0,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $results = $response->json();

        if (! is_array($results) || $results === []) {
            return null;
        }

        $first = $results[0];

        return [
            'lat' => (float) ($first['lat'] ?? 0),
            'lng' => (float) ($first['lon'] ?? 0),
            'display_name' => $first['display_name'] ?? $query,
        ];
    }

    private function photonSearch(string $query, float $storeLat, float $storeLng): ?array
    {
        try {
            $client = $this->httpClient([
                'Accept' => 'application/json',
            ]);

            if ($client === null) {
                return null;
            }

            $response = $client->get('https://photon.komoot.io/api/', [
                'q' => $query,
                'limit' => 1,
                'lat' => $storeLat,
                'lon' => $storeLng,
                'bbox' => self::CAMBODIA_BBOX,
                'lang' => 'en',
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $feature = $response->json('features.0');

        if (! is_array($feature)) {
            return null;
        }

        $coordinates = $feature['geometry']['coordinates'] ?? null;

        if (! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        $properties = $feature['properties'] ?? [];

        if (($properties['countrycode'] ?? null) !== 'KH') {
            return null;
        }

        return [
            'lat' => (float) $coordinates[1],
            'lng' => (float) $coordinates[0],
            'display_name' => $this->formatPhotonAddress($properties) ?? $query,
        ];
    }

    private function formatNominatimAddress(array $result): ?string
    {
        $address = $result['address'] ?? [];

        if (is_array($address) && $address !== []) {
            $parts = array_filter([
                isset($address['house_number'], $address['road']) ? trim($address['house_number'].' '.$address['road']) : ($address['road'] ?? $address['pedestrian'] ?? $address['residential'] ?? null),
                $address['quarter'] ?? $address['suburb'] ?? $address['neighbourhood'] ?? null,
                $address['city'] ?? $address['town'] ?? $address['municipality'] ?? $address['county'] ?? null,
                $address['state'] ?? null,
                $address['country'] ?? null,
            ]);

            if ($parts !== []) {
                return implode(', ', array_unique($parts));
            }
        }

        $displayName = $result['display_name'] ?? null;

        return is_string($displayName) && $displayName !== '' ? $displayName : null;
    }

    private function formatPhotonAddress(array $properties): ?string
    {
        $parts = array_filter([
            $properties['housenumber'] ?? null,
            $properties['street'] ?? $properties['name'] ?? null,
            $properties['district'] ?? null,
            $properties['city'] ?? null,
            $properties['state'] ?? null,
            $properties['country'] ?? null,
        ]);

        if ($parts === []) {
            return $properties['name'] ?? null;
        }

        return implode(', ', array_unique($parts));
    }
}
