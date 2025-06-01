<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If the user was deleted after logging in
        if (!$user || !\App\Models\User::find($user->id)) {
            return response()->json([
                'message' => 'User account no longer exists.'
            ], 403);
        }

        return $next($request);
    }
}
