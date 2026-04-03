<?php

namespace App\Http\Controllers;

use App\Services\DashboardGeoJsonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function collection(Request $request, DashboardGeoJsonService $geoJsonService): JsonResponse
    {
        try {
            $geoJson = $geoJsonService->loadCountryCollectionGeoJson();
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => "GeoJSON kolleksiyasini yuklab bo'lmadi.",
            ], 500);
        }

        return response()
            ->json($geoJson)
            ->header('Content-Type', 'application/geo+json')
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function summary(string $country, DashboardGeoJsonService $geoJsonService): JsonResponse
    {
        try {
            return response()->json($geoJsonService->countryDashboardSummary($country));
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => "Davlat bo'yicha ma'lumotlarni yuklab bo'lmadi.",
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
