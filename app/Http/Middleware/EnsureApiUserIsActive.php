<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiUserIsActive
{
    /**
     * Reject authenticated API access for blocked users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (int) $user->active !== 1) {
            $currentToken = $user->currentAccessToken();
            if ($currentToken) {
                $currentToken->delete();
            }

            return response()->json([
                'response_code' => 403,
                'message' => 'Akun Anda sedang diblokir.',
                'content' => [
                    'id_user' => $user->id_user,
                    'force_logout' => true,
                ],
            ], 403);
        }

        return $next($request);
    }
}
