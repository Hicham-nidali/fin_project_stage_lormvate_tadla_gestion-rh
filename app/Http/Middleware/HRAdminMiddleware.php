<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')
                           ->with('error', 'Vous devez vous connecter pour accéder à cette page');
        }

        // Vérifier si l'utilisateur a le rôle HR Admin
        if (Auth::user()->role !== 'hr_admin') {
            // Rediriger selon le rôle de l'utilisateur
            switch (Auth::user()->role) {
                case 'department_head':
                    return redirect()->route('dashboard')
                                   ->with('error', 'Accès refusé - Privilèges administrateur RH requis');
                case 'employee':
                    return redirect()->route('employee.dashboard')
                                   ->with('error', 'Accès refusé - Privilèges administrateur RH requis');
                default:
                    return redirect()->route('login')
                                   ->with('error', 'Accès refusé - Privilèges administrateur RH requis');
            }
        }

        return $next($request);
    }
}