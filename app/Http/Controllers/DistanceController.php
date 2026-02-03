<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DistanceController extends Controller
{
    public function distance(Request $request)
    {
        // Basic inline validation
        $validated = $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat'   => 'required|numeric',
            'dest_lng'   => 'required|numeric',
        ]);

        $originLat = $validated['origin_lat'];
        $originLng = $validated['origin_lng'];
        $destLat   = $validated['dest_lat'];
        $destLng   = $validated['dest_lng'];

        $apiKey = env('GOOGLE_MAPS_API_KEY');

        if (! $apiKey) {
            return response()->json([
                'message' => 'Google Maps API key not configured.',
            ], 500);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins'      => "{$originLat},{$originLng}",
            'destinations' => "{$destLat},{$destLng}",
            'key'          => $apiKey,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Failed to call Google Maps API.',
            ], 502);
        }

        $data = $response->json();

        if (
            ! isset($data['rows'][0]['elements'][0]['status']) ||
            $data['rows'][0]['elements'][0]['status'] !== 'OK'
        ) {
            return response()->json([
                'message' => 'No route found between the given points.',
            ], 422);
        }

        $element = $data['rows'][0]['elements'][0];

        return response()->json([
            'distance_meters'  => (int) ($element['distance']['value'] ?? 0),
            'distance_text'    => (string) ($element['distance']['text'] ?? ''),
            'duration_seconds' => (int) ($element['duration']['value'] ?? 0),
            'duration_text'    => (string) ($element['duration']['text'] ?? ''),
        ]);
    }
}