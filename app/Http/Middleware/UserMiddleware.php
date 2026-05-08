<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'user') {
                if ((int) Auth::user()->active !== 1) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->expectsJson() || $request->ajax() || $request->is('user/realtime/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Akun Anda telah diblokir oleh admin.',
                            'data' => [
                                'force_logout' => true,
                            ],
                        ], 403);
                    }

                    return redirect()->route('login')->with('error', 'Akun Anda telah diblokir oleh admin.');
                }

                return $next($request);
            }

            if ($request->expectsJson() || $request->ajax() || $request->is('user/realtime/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses user!',
                ], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki hak akses user!');
        }

        if ($request->expectsJson() || $request->ajax() || $request->is('user/realtime/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
                'data' => [
                    'force_logout' => true,
                ],
            ], 401);
        }

        return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
    }
}
