<?php

namespace App\Http\Controllers;

use App\Services\DashboardGeoJsonService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class DashboardGeoJsonController extends Controller
{
    public function index(DashboardGeoJsonService $geoJsonService): JsonResponse
    {
        try {
            return response()->json([
                'data' => $geoJsonService->listCountries(),
            ]);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => "GeoJSON ro'yxatini yuklab bo'lmadi.",
            ], 500);
        }
    }

    public function show(string $country, DashboardGeoJsonService $geoJsonService): JsonResponse
    {
        try {
            $geoJson = $geoJsonService->loadCountryGeoJson($country);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => "GeoJSON faylini o'qib bo'lmadi.",
            ], 500);
        }

        if ($geoJson === null) {
            return response()->json([
                'message' => 'Davlat GeoJSON fayli topilmadi.',
            ], 404);
        }

        return response()
            ->json($geoJson)
            ->header('Content-Type', 'application/geo+json');
    }
}
