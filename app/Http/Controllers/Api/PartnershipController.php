<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partnership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PartnershipController extends Controller
{
    // ── Send Invite ──
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string',
        ]);

        $sender = $request->user();

        // Can't invite yourself
        if ($sender->invite_code === $validated['invite_code']) {
            return response()->json([
                'message' => 'You cannot invite yourself.',
            ], 422);
        }

        // Find the partner by invite code
        $partner = User::where('invite_code', $validated['invite_code'])->first();

        if (!$partner) {
            return response()->json([
                'message' => 'Invalid invite code. No user found.',
            ], 404);
        }

        // Check sender doesn't already have an active partnership
        if ($this->hasActivePartnership($sender->id)) {
            return response()->json([
                'message' => 'You are already in a partnership.',
            ], 422);
        }

        // Check partner doesn't already have an active partnership
        if ($this->hasActivePartnership($partner->id)) {
            return response()->json([
                'message' => 'This person is already in a partnership.',
            ], 422);
        }

        // Check if invite already exists between these two
        $existing = Partnership::where(function ($q) use ($sender, $partner) {
            $q->where('user_a_id', $sender->id)
              ->where('user_b_id', $partner->id);
        })->orWhere(function ($q) use ($sender, $partner) {
            $q->where('user_a_id', $partner->id)
              ->where('user_b_id', $sender->id);
        })->where('status', 'pending')->first();

        if ($existing) {
            return response()->json([
                'message' => 'Invite already sent.',
            ], 422);
        }

        // Create the partnership invite
        $partnership = Partnership::create([
            'user_a_id'    => $sender->id,
            'user_b_id'    => $partner->id,
            'initiated_by' => $sender->id,
            'status'       => 'pending',
        ]);

        return response()->json([
            'message'     => 'Invite sent successfully!',
            'partnership' => $partnership->load(['userA', 'userB']),
        ], 201);
    }

    // ── Accept Invite ──
    public function accept(Request $request)
    {
        $user = $request->user();

        // Find pending invite where this user is the recipient
        $partnership = Partnership::where('user_b_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$partnership) {
            return response()->json([
                'message' => 'No pending invite found.',
            ], 404);
        }

        $partnership->update([
            'status'       => 'active',
            'connected_at' => Carbon::now(),
        ]);

        return response()->json([
            'message'     => 'Partnership accepted! You are now connected.',
            'partnership' => $partnership->load(['userA', 'userB']),
        ]);
    }

    // ── Get My Partnership ──
    public function me(Request $request)
    {
        $user = $request->user();

        $partnership = Partnership::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
              ->orWhere('user_b_id', $user->id);
        })->where('status', 'active')
          ->with(['userA', 'userB'])
          ->first();

        if (!$partnership) {
            return response()->json([
                'message'     => 'No active partnership found.',
                'partnership' => null,
            ]);
        }

        $partner = $partnership->getPartner($user->id);

        return response()->json([
            'partnership' => $partnership,
            'partner'     => $partner,
        ]);
    }

    // ── Leave Partnership ──
    public function leave(Request $request)
    {
        $user = $request->user();

        $partnership = Partnership::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
              ->orWhere('user_b_id', $user->id);
        })->where('status', 'active')->first();

        if (!$partnership) {
            return response()->json([
                'message' => 'No active partnership to leave.',
            ], 404);
        }

        $partnership->update(['status' => 'dissolved']);

        return response()->json([
            'message' => 'Partnership dissolved.',
        ]);
    }

    // ── Get Pending Invites ──
    public function pending(Request $request)
    {
        $user = $request->user();

        $invites = Partnership::where('user_b_id', $user->id)
            ->where('status', 'pending')
            ->with('userA')
            ->get();

        return response()->json([
            'invites' => $invites,
        ]);
    }

    // ── Helper ──
    private function hasActivePartnership(string $userId): bool
    {
        return Partnership::where(function ($q) use ($userId) {
            $q->where('user_a_id', $userId)
              ->orWhere('user_b_id', $userId);
        })->where('status', 'active')->exists();
    }
}