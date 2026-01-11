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

                return [
                    'address' => $data['display_name'] ?? null,
                    'subdistrict' => $address['village'] ?? $address['suburb'] ?? $address['neighbourhood'] ?? null,
                    'district' => $address['subdistrict'] ?? $address['district'] ?? $address['city_district'] ?? null,
                    'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? $address['county'] ?? null,
                    'region' => $address['state'] ?? $address['province'] ?? null,
                    'country' => $address['country'] ?? null,
                    'country_code' => strtoupper($address['country_code'] ?? ''),
                    'postal_code' => $address['postcode'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Reverse geocoding failed: ' . $e->getMessage());
        }

        return [];
    }
}
