<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'password'     => $request->password, // auto-hashed via model cast
            'account_type' => $request->account_type,
            'is_approved'  => $request->account_type === 'buyer', // buyers auto-approved, sellers need review
        ]);

        // Assign role based on account type
        $role = $request->account_type === 'seller' ? 'seller' : 'buyer';
        $user->assignRole($role);

        // Only issue a token for buyers (sellers must wait for admin approval)
        $token = null;
        if ($request->account_type === 'buyer') {
            $token = $user->createToken('auth_token')->plainTextToken;
        }

        return response()->json([
            'success' => true,
            'message' => $request->account_type === 'seller'
                ? 'تم التسجيل بنجاح. في انتظار موافقة الإدارة.'
                : 'تم التسجيل بنجاح!',
            'data' => [
                'user'         => new UserResource($user),
                'token'        => $token,
                'token_type'   => $token ? 'Bearer' : null,
                'needs_approval' => $request->account_type === 'seller',
            ],
        ], 201);
    }

    /**
     * Login user and return API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            ]);
        }

        // Block inactive accounts
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك معطّل. يرجى التواصل مع الإدارة.',
            ], 403);
        }

        // Block unapproved sellers
        if ($user->account_type === 'seller' && !$user->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك قيد المراجعة. يرجى انتظار موافقة الإدارة.',
            ], 403);
        }

        // Revoke all old tokens and issue a fresh one (prevents session accumulation)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح!',
            'data'    => [
                'user'       => new UserResource($user->load('roles')),
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout: revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح!',
        ]);
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource($request->user()->load('roles', 'permissions')),
        ]);
    }
}
