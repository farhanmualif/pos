<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (Auth::guard($guards)->check()) {
            if (Auth::user()->akses == 1) {
                return redirect("/super_admin/dashboard");
            } else if (Auth::user()->akses == 2) {
                return redirect("/admin/dashboard");
            } else if (Auth::user()->akses == 3) {
                return redirect("/karyawan/dashboard");
            }
        }

        return $next($request);
    }
}
