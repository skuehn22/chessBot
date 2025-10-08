<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MapillaryController extends Controller
{
    private $accessToken;
    private $clientId;
    
    public function __construct()
    {
        $this->accessToken = env('MAPILLARY_ACCESS_TOKEN', 'MLY|10040112542755559|6fdad3c6b49a914484d2dc604d597307');
        $this->clientId = env('MAPILLARY_CLIENT_ID', '10040112542755559');
    }
    
    /**
     * Display the Mapillary test page
     */
    public function index()
    {
        return view('mapillary');
    }
    
    /**
     * Search for Mapillary images in Berlin area
     * Focus on areas likely to have scaffolding (construction zones)
     */
    public function searchBerlinImages(Request $request): JsonResponse
    {
        try {
            // Berlin city center coordinates
            $lat = $request->input('lat', 52.520008);
            $lng = $request->input('lng', 13.404954);
            $radius = $request->input('radius', 500); // meters
            
            // Create bounding box from center point and radius
            $bbox = $this->calculateBoundingBox($lat, $lng, $radius);
            
            // Cache key for this specific search
            $cacheKey = "mapillary_images_{$lat}_{$lng}_{$radius}";
            
            // Try to get cached data first (15 minute cache)
            $images = Cache::remember($cacheKey, 900, function () use ($bbox) {
                return $this->fetchMapillaryImages($bbox);
            });
            
            // Process images to identify potential scaffolding locations
            $processedImages = $this->processImagesForScaffolding($images);
            
            return response()->json([
                'success' => true,
                'center' => ['lat' => $lat, 'lng' => $lng],
                'radius' => $radius,
                'total_images' => count($images),
                'scaffolding_potential' => count($processedImages),
                'images' => $processedImages
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mapillary search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch Mapillary data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get images for a specific area in Berlin
     */
    public function getAreaImages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'bbox' => 'required|string', // Format: minLng,minLat,maxLng,maxLat
                'limit' => 'integer|min:1|max:100'
            ]);
            
            $bbox = $request->input('bbox');
            $limit = $request->input('limit', 50);
            
            $images = $this->fetchMapillaryImages($bbox, $limit);
            
            return response()->json([
                'success' => true,
                'count' => count($images),
                'images' => $images
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mapillary area fetch error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch area images',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Fetch images from Mapillary API
     */
    private function fetchMapillaryImages($bbox, $limit = 50): array
    {
        try {
            // Mapillary API v4 endpoint
            $url = 'https://graph.mapillary.com/images';
            
            $response = Http::withHeaders([
                'Authorization' => 'OAuth ' . $this->accessToken,
            ])->get($url, [
                'bbox' => $bbox,
                'fields' => 'id,geometry,captured_at,compass_angle,thumb_2048_url,thumb_1024_url',
                'limit' => $limit
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }
            
            Log::warning('Mapillary API returned non-success: ' . $response->body());
            return [];
            
        } catch (\Exception $e) {
            Log::error('Mapillary API error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate bounding box from center point and radius
     */
    private function calculateBoundingBox($centerLat, $centerLng, $radiusMeters): string
    {
        // Rough approximation: 1 degree latitude = 111km
        // 1 degree longitude at Berlin's latitude ≈ 67km
        $latDelta = ($radiusMeters / 1000) / 111;
        $lngDelta = ($radiusMeters / 1000) / 67;
        
        $minLat = $centerLat - $latDelta;
        $maxLat = $centerLat + $latDelta;
        $minLng = $centerLng - $lngDelta;
        $maxLng = $centerLng + $lngDelta;
        
        return "{$minLng},{$minLat},{$maxLng},{$maxLat}";
    }
    
    /**
     * Process images to identify potential scaffolding locations
     */
    private function processImagesForScaffolding($images): array
    {
        $processed = [];
        
        foreach ($images as $image) {
            // Extract coordinates
            $coords = $image['geometry']['coordinates'] ?? null;
            if (!$coords) continue;
            
            // Prepare simplified image data
            $processed[] = [
                'id' => $image['id'],
                'lat' => $coords[1],
                'lng' => $coords[0],
                'captured_at' => $image['captured_at'] ?? null,
                'compass_angle' => $image['compass_angle'] ?? 0,
                'thumb_url' => $image['thumb_1024_url'] ?? $image['thumb_2048_url'] ?? null,
                // TODO: Add ML-based scaffolding detection score here
                'scaffolding_probability' => $this->mockScaffoldingScore()
            ];
        }
        
        // Sort by scaffolding probability (highest first)
        usort($processed, function($a, $b) {
            return $b['scaffolding_probability'] - $a['scaffolding_probability'];
        });
        
        return $processed;
    }
    
    /**
     * Mock scaffolding detection score - replace with actual ML model
     */
    private function mockScaffoldingScore(): float
    {
        // Random score for now - replace with actual detection
        return round(mt_rand(0, 100) / 100, 2);
    }
    
    /**
     * Get popular construction areas in Berlin
     */
    public function getConstructionHotspots(): JsonResponse
    {
        // Known construction/renovation hotspots in Berlin
        $hotspots = [
            [
                'name' => 'Alexanderplatz',
                'lat' => 52.521992,
                'lng' => 13.413244,
                'description' => 'Major construction and renovation area'
            ],
            [
                'name' => 'Potsdamer Platz',
                'lat' => 52.509669,
                'lng' => 13.376294,
                'description' => 'Ongoing development projects'
            ],
            [
                'name' => 'Friedrichshain',
                'lat' => 52.515816,
                'lng' => 13.454065,
                'description' => 'Gentrification and renovation zone'
            ],
            [
                'name' => 'Prenzlauer Berg',
                'lat' => 52.538906,
                'lng' => 13.424472,
                'description' => 'Historic building renovations'
            ],
            [
                'name' => 'Kreuzberg',
                'lat' => 52.499259,
                'lng' => 13.403883,
                'description' => 'Mixed renovation projects'
            ]
        ];
        
        return response()->json([
            'success' => true,
            'hotspots' => $hotspots
        ]);
    }
}