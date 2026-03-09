<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── Register ──
    public function register(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|min:2|max:100',
            'email'        => 'required|email:rfc,dns|unique:users,email|max:200',
            'password'     => [
                'required',
                'confirmed',
                'min:8',
                'max:64',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[0-9]/',      // at least one number
            ],
        ], [
            'email.email'          => 'Please enter a valid email address.',
            'password.regex'       => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.min'         => 'Password must be at least 8 characters.',
            'display_name.min'     => 'Name must be at least 2 characters.',
        ]);
    
        $user = User::create([
            'display_name' => $validated['display_name'],
            'email'        => strtolower(trim($validated['email'])),
            'password'     => Hash::make($validated['password']),
            'invite_code'  => 'TRA-' . strtoupper(Str::random(8)),
        ]);
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Account created successfully!',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // ── Login ──
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('tara-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    // ── Logout ──
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    // ── Me (get current user) ──
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // ── Helpers ──
    private function generateInviteCode(): string
    {
        do {
            $code = 'TRA-' . strtoupper(Str::random(4));
        } while (User::where('invite_code', $code)->exists());

        return $code;
    }
}