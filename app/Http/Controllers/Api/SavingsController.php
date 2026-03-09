<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use App\Models\SavingsContribution;
use App\Models\Partnership;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    // ── Helper: Get active partnership ──
    // private function getPartnership(Request $request)
    // {
    //     $user = $request->user();

    //     return Partnership::where(function ($q) use ($user) {
    //         $q->where('user_a_id', $user->id)
    //           ->orWhere('user_b_id', $user->id);
    //     })->where('status', 'active')->first();
    // }

    // ── List Goals ──
    public function index(Request $request)
    {
        $partnership = $request->partnership;

        if (!$partnership) {
            return response()->json([
                'message' => 'No active partnership.',
                'goals'   => [],
            ]);
        }

        $goals = SavingsGoal::where('partnership_id', $partnership->id)
            ->with(['contributions.contributor', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['goals' => $goals]);
    }

    // ── Create Goal ──
    public function store(Request $request)
    {
        $partnership = $request->partnership;

        if (!$partnership) {
            return response()->json([
                'message' => 'You need an active partnership to create savings goals.',
            ], 422);
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:200',
            'emoji'          => 'nullable|string|max:10',
            'target_amount'  => 'required|numeric|min:1',
            'target_date'    => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $goal = SavingsGoal::create([
            'partnership_id' => $partnership->id,
            'created_by'     => $request->user()->id,
            'title'          => $validated['title'],
            'emoji'          => $validated['emoji'] ?? '💰',
            'target_amount'  => $validated['target_amount'],
            'target_date'    => $validated['target_date'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Savings goal created!',
            'goal'    => $goal->load(['contributions.contributor', 'creator']),
        ], 201);
    }

    // ── Delete Goal ──
    public function destroy(Request $request, string $id)
    {
        $partnership = $request->partnership;

        $goal = SavingsGoal::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $goal->delete();

        return response()->json(['message' => 'Goal deleted.']);
    }

    // ── Add Contribution ──
    public function addContribution(Request $request, string $id)
    {
        $partnership = $request->partnership;

        $goal = SavingsGoal::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes'  => 'nullable|string|max:200',
        ]);

        $contribution = SavingsContribution::create([
            'goal_id'         => $goal->id,
            'contributed_by'  => $request->user()->id,
            'amount'          => $validated['amount'],
            'notes'           => $validated['notes'] ?? null,
            'contributed_at'  => now(),
        ]);

        return response()->json([
            'message'      => 'Contribution added!',
            'contribution' => $contribution->load('contributor'),
            'goal'         => $goal->fresh()->load(['contributions.contributor', 'creator']),
        ], 201);
    }

    // ── Delete Contribution ──
    public function deleteContribution(Request $request, string $id, string $contributionId)
    {
        $partnership = $request->partnership;

        $goal = SavingsGoal::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $contribution = SavingsContribution::where('id', $contributionId)
            ->where('goal_id', $goal->id)
            ->first();

        if (!$contribution) {
            return response()->json(['message' => 'Contribution not found.'], 404);
        }

        $contribution->delete();

        return response()->json([
            'message' => 'Contribution removed.',
            'goal'    => $goal->fresh()->load(['contributions.contributor', 'creator']),
        ]);
    }
}