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

        if($regions->isEmpty()) {
            return $this->sendResponse($regions, 'Regions not found.', false);
        }

        return $this->sendResponse($regions, 'Regions retrieved successfully.');
    }

    /**
     * Retrieve a single region
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $region = Region::query()->where('id', $id)->get();

        if($region->isEmpty()) {
            return $this->sendResponse($region, 'Region not found.', false);
        }

        return $this->sendResponse($region, 'Region retrieved successfully.');
    }
}
