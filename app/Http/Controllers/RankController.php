<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $ranks = Rank::query()->orderBy('id')->get();

        return response()->json($ranks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_cryl' => ['required', 'string', 'max:255'],
        ]);

        $rank = Rank::create($validated);

        return response()->json($rank, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rank $rank): JsonResponse
    {
        return response()->json($rank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rank $rank): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => ['sometimes', 'required', 'string', 'max:255'],
            'name_uz' => ['sometimes', 'required', 'string', 'max:255'],
            'name_cryl' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $rank->update($validated);

        return response()->json($rank);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank): JsonResponse
    {
        $rank->delete();

        return response()->json([
            'message' => 'Rank deleted successfully.',
        ]);
    }
}
