<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
                            'companies:view', 'companies:edit', 'companies:add'
                        ])->plainTextToken
                ]
            );
        }
        return response()->json([
                'success' => true,
                'message' => 'Login',
                'token' => $user->createToken("applicant-$request->device_name",
                    [])->plainTextToken
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
