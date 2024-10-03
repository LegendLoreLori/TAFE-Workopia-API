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
use Illuminate\Validation\Rules\Password;

/**
 * @group User
 *
 * API endpoints for users
 */
class UserController extends Controller
{
    /**
     * List all users
     *
     * @return JsonResponse
     */
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
     * Add a user to the database
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,255',
            'family_name' => 'required|string|between:2,255',
            'username' => 'required|string|unique:users|min:2',
            'email' => 'required|email:rfc',
            'password' => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],
            'password_confirmation' => 'required',
            'type' => [
                'required', 'string', Rule::in(["Client", "Staff", "Applicant"])
            ],
            'company_id' => 'bail|exclude_unless:type,Client|integer|exists:companies,id',
            'status' => [
                'required', 'string', Rule::in([
                    'Active', 'Unconfirmed', 'Unknown', 'Suspended', 'Banned'
                ])
            ],
        ]);

        if ($validator->fails()) {
            $messages[] = [
                $validator->errors()->messages(), $validator->attributes()
            ];
            Log::debug("message bag", $messages);
            return self::sendFailure($messages, 422);
        }

        $validated = $validator->safe()->all();
        $user = new UserResource(User::create($validated));

        return self::sendSuccess($user,
            "User with id: $user->id created", 201);
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
