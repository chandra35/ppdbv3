<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;

class VisitorLocationController extends Controller
{
    /**
     * Store visitor location from browser GPS
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'altitude_accuracy' => 'nullable|numeric',
            'heading' => 'nullable|numeric|between:0,360',
            'speed' => 'nullable|numeric',
            'session_id' => 'required|string',
        ]);

        // Update the latest visitor log with this session that doesn't have GPS coordinates yet
        $visitorLog = VisitorLog::where('session_id', $validated['session_id'])
            ->where(function ($query) {
                $query->where('location_source', '!=', 'gps')
                      ->orWhereNull('location_source');
            })
            ->latest('visited_at')
            ->first();

        if ($visitorLog) {
            // Get detailed address from coordinates using reverse geocoding
            $geoData = $this->reverseGeocode($validated['latitude'], $validated['longitude']);
            
            $visitorLog->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'altitude' => $validated['altitude'] ?? null,
                'accuracy' => $validated['accuracy'] ?? null,
                'altitude_accuracy' => $validated['altitude_accuracy'] ?? null,
                'heading' => $validated['heading'] ?? null,
                'speed' => $validated['speed'] ?? null,
                'location_source' => 'gps',
                'city' => $geoData['city'] ?? $visitorLog->city,
                'district' => $geoData['district'] ?? null,
                'subdistrict' => $geoData['subdistrict'] ?? null,
                'region' => $geoData['region'] ?? $visitorLog->region,
                'country' => $geoData['country'] ?? $visitorLog->country,
                'country_code' => $geoData['country_code'] ?? $visitorLog->country_code,
                'address' => $geoData['address'] ?? null,
                'postal_code' => $geoData['postal_code'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => [
                    'coordinates' => [
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                    ],
                    'address' => $geoData['address'] ?? null,
                    'accuracy' => $validated['accuracy'] ?? null,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No visitor log found to update',
        ], 404);
    }

    /**
     * Reverse geocode coordinates to get detailed address
     * 
     * Note: Nominatim returns different field names for different countries.
     * For Indonesia:
     * - village = Kelurahan/Desa
     * - city_district = Kecamatan
     * - city/town/municipality = Kota/Kabupaten
     * - county = sometimes used for Kabupaten
     * - state = Provinsi
     */
    protected function reverseGeocode(float $lat, float $lng): array
    {
        try {
            // Using Nominatim for detailed address
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'PPDB-System/1.0',
                ])
                ->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 18, // Max detail
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];
                $countryCode = strtoupper($address['country_code'] ?? '');

                // Indonesian specific mapping
                if ($countryCode === 'ID') {
                    return $this->parseIndonesianAddress($data, $address);
                }

                // Default/international mapping
                return [
                    'address' => $data['display_name'] ?? null,
                    'subdistrict' => $address['village'] ?? $address['suburb'] ?? $address['neighbourhood'] ?? null,
                    'district' => $address['city_district'] ?? $address['district'] ?? $address['subdistrict'] ?? null,
                    'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
                    'region' => $address['state'] ?? $address['province'] ?? $address['county'] ?? null,
                    'country' => $address['country'] ?? null,
                    'country_code' => $countryCode,
                    'postal_code' => $address['postcode'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Reverse geocoding failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Parse Indonesian address from Nominatim response
     * Indonesia uses: Kelurahan/Desa -> Kecamatan -> Kota/Kabupaten -> Provinsi
     */
    protected function parseIndonesianAddress(array $data, array $address): array
    {
        // Kelurahan/Desa (village level)
        $kelurahan = $address['village'] ?? $address['suburb'] ?? $address['neighbourhood'] ?? null;
        
        // Kecamatan (subdistrict level) - Nominatim often puts this in city_district
        $kecamatan = $address['city_district'] ?? $address['subdistrict'] ?? null;
        
        // Kota/Kabupaten (city/regency level)
        // Priority: city > town > municipality
        $kotaKab = $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null;
        
        // If no city found, use county (which is usually Kabupaten name in Indonesia)
        // County in Indonesia context is typically a regency/kabupaten name like "Lampung Timur"
        if (!$kotaKab && isset($address['county'])) {
            $kotaKab = $address['county'];
        }
        
        // Provinsi (state/province level)
        $provinsi = $address['state'] ?? $address['province'] ?? null;
        
        // If we still don't have a city, try to extract from display_name
        if (!$kotaKab && isset($data['display_name'])) {
            $kotaKab = $this->extractCityFromDisplayName($data['display_name'], $provinsi);
        }

        return [
            'address' => $data['display_name'] ?? null,
            'subdistrict' => $kelurahan,
            'district' => $kecamatan,
            'city' => $kotaKab,
            'region' => $provinsi,
            'country' => $address['country'] ?? 'Indonesia',
            'country_code' => 'ID',
            'postal_code' => $address['postcode'] ?? null,
        ];
    }

    /**
     * Try to extract city/kabupaten name from display_name
     * Display name format: "Detail, Kelurahan, Kecamatan, Kota/Kab, Provinsi, Country"
     */
    protected function extractCityFromDisplayName(?string $displayName, ?string $provinsi): ?string
    {
        if (!$displayName) return null;
        
        $parts = array_map('trim', explode(',', $displayName));
        
        // Look for parts containing "Kabupaten" or "Kota"
        foreach ($parts as $part) {
            $lower = strtolower($part);
            if (str_contains($lower, 'kabupaten') || 
                (str_contains($lower, 'kota') && !str_contains($lower, 'kecamatan'))) {
                return $part;
            }
        }
        
        // If provinsi is known, the city is usually right before it
        if ($provinsi) {
            $provinsiIndex = array_search($provinsi, $parts);
            if ($provinsiIndex !== false && $provinsiIndex > 0) {
                return $parts[$provinsiIndex - 1];
            }
        }
        
        return null;
    }
}
