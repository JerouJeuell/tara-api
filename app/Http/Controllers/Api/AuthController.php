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
            'display_name' => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'display_name' => $validated['display_name'],
            'email'        => $validated['email'],
            'password_hash'=> Hash::make($validated['password']),
            'invite_code'  => $this->generateInviteCode(),
        ]);

        $token = $user->createToken('tara-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
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