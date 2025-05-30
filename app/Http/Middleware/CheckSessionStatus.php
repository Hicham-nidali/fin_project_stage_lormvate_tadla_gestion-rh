<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est censé être connecté
        if ($request->expectsJson()) {
            return $next($request);
        }
        
        // Si la route nécessite une authentification
        if ($request->route() && in_array('auth', $request->route()->middleware())) {
            if (!Auth::check()) {
                // Nettoyer la session corrompue
                $request->session()->flush();
                $request->session()->regenerate();
                
                return redirect()->route('login')
                    ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
            }
            
            // Vérifier que l'utilisateur existe toujours en base
            $user = Auth::user();
            if (!$user || !$user->exists) {
                Auth::logout();
                $request->session()->flush();
                
                return redirect()->route('login')
                    ->with('error', 'Votre compte n\'est plus valide. Veuillez vous reconnecter.');
            }
        }
        
        return $next($request);
    }
}