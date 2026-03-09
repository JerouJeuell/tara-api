<?php

namespace App\Http\Middleware;

use App\Models\Partnership;
use Closure;
use Illuminate\Http\Request;

class RequiresActivePartnership
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $partnership = Partnership::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
              ->orWhere('user_b_id', $user->id);
        })->where('status', 'active')->first();

        if (!$partnership) {
            return response()->json([
                'message' => 'You need an active partnership to perform this action.',
            ], 403);
        }

        $request->merge(['partnership' => $partnership]);

        return $next($request);
    }
}