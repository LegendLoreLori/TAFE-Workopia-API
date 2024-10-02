<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group User
 *
 * API endpoints for users
 */
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return self::sendFailure('Unable to retrieve users at this time, please contact your system administrator',
                418);
        }

        return self::sendSuccess(UserResource::collection($users),
            'Retrieved users', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
