<?php

namespace App\Http\Controllers;

use App\Http\Resources\PositionResource;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{

    /**
     * List all positions
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $positions = Position::with('company')->get();

        if ($positions->isEmpty()) {
            return self::sendFailure('Unable to retrieve positions at this time, please contact your system administrator', 418);
        }

        return self::sendSuccess(PositionResource::collection($positions), 'Retrieved positions', 200);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Position $position)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }


    public function restore(string $id)
    {
        //
    }
}
