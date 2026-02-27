<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTag;
use App\Models\Partnership;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // ── Helper: Get active partnership or fail ──
    private function getPartnership(Request $request)
    {
        $user = $request->user();

        $partnership = Partnership::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
              ->orWhere('user_b_id', $user->id);
        })->where('status', 'active')->first();

        return $partnership;
    }

    // ── List Events ──
    public function index(Request $request)
    {
        $partnership = $this->getPartnership($request);

        if (!$partnership) {
            return response()->json([
                'message' => 'No active partnership found.',
                'events'  => [],
            ]);
        }

        $events = Event::where('partnership_id', $partnership->id)
            ->with(['tags', 'creator'])
            ->orderBy('event_date', 'asc')
            ->get();

        return response()->json(['events' => $events]);
    }

    // ── Create Event ──
    public function store(Request $request)
    {
        $partnership = $this->getPartnership($request);

        if (!$partnership) {
            return response()->json([
                'message' => 'You need an active partnership to create events.',
            ], 422);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'event_date'  => 'required|date',
            'event_time'  => 'nullable|date_format:H:i',
            'venue'       => 'nullable|string|max:200',
            'notes'       => 'nullable|string',
            'emoji'       => 'nullable|string|max:10',
            'is_recurring'=> 'boolean',
            'tags'        => 'nullable|array',
            'tags.*.label'=> 'required|string|max:50',
            'tags.*.color'=> 'required|in:rose,gold,green,blue,purple',
        ]);

        $event = Event::create([
            'partnership_id' => $partnership->id,
            'created_by'     => $request->user()->id,
            'title'          => $validated['title'],
            'event_date'     => $validated['event_date'],
            'event_time'     => $validated['event_time'] ?? null,
            'venue'          => $validated['venue'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'emoji'          => $validated['emoji'] ?? '📅',
            'is_recurring'   => $validated['is_recurring'] ?? false,
        ]);

        // Create tags if provided
        if (!empty($validated['tags'])) {
            foreach ($validated['tags'] as $tag) {
                EventTag::create([
                    'event_id' => $event->id,
                    'label'    => $tag['label'],
                    'color'    => $tag['color'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Event created successfully!',
            'event'   => $event->load(['tags', 'creator']),
        ], 201);
    }

    // ── Get Single Event ──
    public function show(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $event = Event::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->with(['tags', 'creator'])
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json(['event' => $event]);
    }

    // ── Update Event ──
    public function update(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $event = Event::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:200',
            'event_date'  => 'sometimes|date',
            'event_time'  => 'nullable|date_format:H:i',
            'venue'       => 'nullable|string|max:200',
            'notes'       => 'nullable|string',
            'emoji'       => 'nullable|string|max:10',
            'is_recurring'=> 'boolean',
            'tags'        => 'nullable|array',
            'tags.*.label'=> 'required_with:tags|string|max:50',
            'tags.*.color'=> 'required_with:tags|in:rose,gold,green,blue,purple',
        ]);

        $event->update($validated);

        // Replace tags if provided
        if (isset($validated['tags'])) {
            $event->tags()->delete();
            foreach ($validated['tags'] as $tag) {
                EventTag::create([
                    'event_id' => $event->id,
                    'label'    => $tag['label'],
                    'color'    => $tag['color'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Event updated successfully!',
            'event'   => $event->load(['tags', 'creator']),
        ]);
    }

    // ── Delete Event ──
    public function destroy(Request $request, string $id)
    {
        $partnership = $this->getPartnership($request);

        $event = Event::where('id', $id)
            ->where('partnership_id', $partnership?->id)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}