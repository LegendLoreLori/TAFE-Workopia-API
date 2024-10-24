<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @group Sanctum
 *
 * API endpoints for user facing interactions
 */
class AuthController extends Controller
{
    /**
     * Register a new user of type client or applicant
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,255',
            'family_name' => 'required|string|between:2,255',
            'username' => 'required|string|unique:users|min:2',
            'email' => 'required|email:rfc|unique:users',
            'password' => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],
            'password_confirmation' => 'required',
            'type' => [
                'required', 'string', Rule::in(["Client", "Applicant"])
            ],
            'company_id' => 'bail|exclude_unless:type,Client|integer|exists:companies,id',
        ]);

        if ($validator->fails()) {
            $messages[] = [
                $validator->errors()->messages(), $validator->attributes()
            ];
            Log::debug("message bag", $messages);
            return self::sendFailure($messages, 422);
        }

        $validated = $validator->safe()->merge(['status' => 'Active'])->all();
        $user = new UserResource(User::create($validated));

        return self::sendSuccess($user,
            "User with id: $user->id created", 201);
    }

    /**
     * Create a session token for an existing user
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return self::sendFailure($messages, 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([

                'message' => 'Login invalid'
            ], 503);
        }

        if ($user->type == 'Staff') {
            return response()->json([
                    'success' => true,
                    'message' => 'Login',
                    'token' => $user->createToken("staff-$request->device_name",
                        [
                            'users:administer',
                            'positions:administer',
                            'companies:administer'
                        ])->plainTextToken
                ]
            );
        }
        if ($user->type == 'Client') {
            return response()->json([
                    'success' => true,
                    'message' => 'Login',
                    'token' => $user->createToken("client-$request->device_name",
                        [
                            'companies:view', 'companies:edit', 'companies:add',
                            'positions:view', 'positions:edit', 'positions:add',
                            'users:view', 'users:edit'
                        ])->plainTextToken
                ]
            );
        }
        return response()->json([
                'success' => true,
                'message' => 'Login',
                'token' => $user->createToken("applicant-$request->device_name",
                    [
                        'positions:view', 'users:view', 'users:edit'
                    ])->plainTextToken
            ]
        );
    }

    /**
     * Delete the session token for the currently authenticated user
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout'
        ]);
    }
}
