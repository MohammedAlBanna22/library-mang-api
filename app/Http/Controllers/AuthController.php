<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user  = User::create([
            ...$validated,
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user  = Auth::user();
       // $user->tokens()->delete(); // Optional: Revoke previous tokens on login
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Logged in successfully',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   =>  new UserResource($request->user()),
        ]);
    }


   public function refreshToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
            'status'  => false,
            'message' => 'Unauthenticated.',
        ], 401);
        }

        $currentToken = $request->user()->currentAccessToken();

        // تحقق إن التوكن مش منتهي (إذا عندك expiration)
        if ($currentToken->expires_at && $currentToken->expires_at->isPast()) {
            $currentToken->delete();

            return response()->json([
                'status'  => false,
                'message' => 'Token has expired. Please login again.',
            ], 401);
        }

        // احذف التوكن الحالي وأنشئ جديد
        $currentToken->delete();

        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => true,
            'message'      => 'Token refreshed successfully.',
            'access_token' => $newToken,
            'token_type'   => 'Bearer',
        ]);
    }


    public function forgotPassword(Request $request)
    {
         $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    // Temporary debug line
    \Log::info('Password reset status: ' . $status);

    if ($status !== Password::RESET_LINK_SENT) {
        return response()->json([
            'status'  => false,
            'message' => __($status), // ← show exact error
        ], 500);
    }

    return response()->json([
        'status'  => true,
        'message' => 'Password reset link sent to your email.',
    ]);
    }

    public function resetPassword(Request $request)
{
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email|exists:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }
    );

    if ($status !== Password::PASSWORD_RESET) {
        return response()->json([
            'status'  => false,
            'message' => __($status),
        ], 400);
    }

    return response()->json([
        'status'  => true,
        'message' => 'Password has been reset successfully.',
    ]);
}


}