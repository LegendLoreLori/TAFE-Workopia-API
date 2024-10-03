<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
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
     * Retrieve a single user
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if ($user === null) {
            return self::sendFailure('Specified user not found', 404);
        }

        return self::sendSuccess(new UserResource($user),
            "Retrieved user with id: $user->id");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Soft delete the specified user from the database
     *
     * @urlParam id integer required The ID of the user.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if ($user === null) {
            return self::sendFailure("Specified user not found", 404);
        }
        $user->delete();

        return self::sendSuccess(new UserResource($user), "User with id: $user->id deleted", 200);
    }

    /**
     * Restore the specified soft deleted user from trash
     *
     * @urlParam id integer required The ID of the company.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        User::withTrashed()
            ->where('id', $id)
            ->restore();

        return self::sendSuccess($id, "User with id: $id restored", 200);
    }
}
