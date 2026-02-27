<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Partnership;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    // ── Helper: Get active partnership ──
    private function getPartnership(Request $request)
    {
        $user = $request->user();

        return Partnership::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
              ->orWhere('user_b_id', $user->id);
        })->where('status', 'active')->first();
    }

    // ── List Checklists ──
    public function index(Request $request)
    {
        $partnership = $this->getPartnership($request);

        if (!$partnership) {
            return response()->json(['message' => 'No active partnership.', 'checklists' => []]);
        }

        $checklists = Checklist::where('partnership_id', $partnership->id)
            ->with(['items', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['checklists' => $checklists]);
    }

    // ── Create Checklist ──
    public function store(Request $request)
    {
        $partnership = $this->getPartnership($request);

        if (!$partnership) {
            return response()->json([
                'message' => 'You need an active partnership to create checklists.',
            ], 422);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'emoji'       => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'items'       => 'nullable|array',
            'items.*.title' => 'required|string|max:200',
        ]);

        $checklist = Checklist::create([
            'partnership_id' => $partnership->id,
            'created_by'     => $request->user()->id,
            'title'          => $validated['title'],
            'emoji'          => $validated['emoji'] ?? '✅',
            'description'    => $validated['description'] ?? null,
        ]);

        // Create initial items if provided
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $index => $item) {
                ChecklistItem::create([
                    'checklist_id' => $checklist->id,
                    'title'        => $item['title'],
                    'sort_order'   => $index,
                ]);
            }
        }

        return response()->json([
            'message'   => 'Checklist created!',
            'checklist' => $checklist->load(['items', 'creator']),
        ], 201);
    }

    // ── Get Single Checklist ──
    public function show(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $checklist = Checklist::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->with(['items', 'creator'])
            ->first();

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found.'], 404);
        }

        return response()->json(['checklist' => $checklist]);
    }

    // ── Delete Checklist ──
    public function destroy(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $checklist = Checklist::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found.'], 404);
        }

        $checklist->delete();

        return response()->json(['message' => 'Checklist deleted.']);
    }

    // ── Add Item to Checklist ──
    public function addItem(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $checklist = Checklist::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
        ]);

        $item = ChecklistItem::create([
            'checklist_id' => $checklist->id,
            'title'        => $validated['title'],
            'sort_order'   => $checklist->items()->count(),
        ]);

        return response()->json([
            'message' => 'Item added!',
            'item'    => $item,
        ], 201);
    }

    // ── Toggle Item Complete ──
    public function toggleItem(Request $request, string $id, string $itemId)
    {
        $partnership = $this->getPartnership($request);

        $checklist = Checklist::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found.'], 404);
        }

        $item = ChecklistItem::where('id', $itemId)
            ->where('checklist_id', $checklist->id)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_by' => !$item->is_completed ? $request->user()->id : null,
            'completed_at' => !$item->is_completed ? now() : null,
        ]);

        return response()->json([
            'message' => $item->is_completed ? 'Item completed!' : 'Item unchecked.',
            'item'    => $item,
        ]);
    }

    // ── Delete Item ──
    public function deleteItem(Request $request, string $id, string $itemId)
    {
        $partnership = $this->getPartnership($request);

        $checklist = Checklist::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found.'], 404);
        }

        $item = ChecklistItem::where('id', $itemId)
            ->where('checklist_id', $checklist->id)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }
}