<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeMiddleware
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
        if (!Auth::check()) {
            return redirect()->route('login')
                           ->with('error', 'Vous devez vous connecter pour accéder à cette page');
        }

        if (Auth::user()->role !== 'employee') {
            switch (Auth::user()->role) {
                case 'hr_admin':
                    return redirect()->route('hr.dashboard')
                                   ->with('error', 'Accès refusé - Cette section est réservée aux employés');
                case 'department_head':
                    return redirect()->route('dashboard')
                                   ->with('error', 'Accès refusé - Cette section est réservée aux employés');
                default:
                    return redirect()->route('login')
                                   ->with('error', 'Accès refusé - Cette section est réservée aux employés');
            }
        }

        return $next($request);
    }
}