<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Retrieve all regions
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $regions = Region::all();

        if ($regions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Regions not found'
            ], 404);
        }

        return self::sendSuccess($regions, 'Regions retrieved successfully');
    }

    /**
     * Retrieve a single region
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $region = Region::query()->where('id', $id)->get();

        if ($region->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Region with id: $id not found"
            ], 404);
        }

        return self::sendSuccess($region, 'Region retrieved successfully.');
    }
}
