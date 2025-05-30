<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Si déjà connecté, rediriger selon le rôle
        if (Auth::check()) {
            return $this->redirectToUserDashboard();
        }
        
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        // Tenter la connexion avec "remember me"
        $remember = $request->filled('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Définir une durée de session plus longue
            if ($remember) {
                config(['session.lifetime' => 43200]); // 30 jours en minutes
            }
            
            return $this->redirectToUserDashboard();
        }
 
        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        
        // Invalider complètement la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Supprimer tous les cookies de session
        $this->clearAllCookies($request);
        
        return redirect('/login')->with('success', 'Vous avez été déconnecté avec succès');
    }
    
    /**
     * Redirection selon le rôle utilisateur
     */
    private function redirectToUserDashboard()
    {
        $user = Auth::user();
        
        switch ($user->role) {
            case 'hr_admin':
                return redirect()->route('hr.dashboard');
            case 'department_head':
                return redirect()->route('dashboard');  
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->with('error', 'Rôle utilisateur non reconnu');
        }
    }
    
    /**
     * Nettoyer tous les cookies
     */
    private function clearAllCookies($request)
    {
        // Supprimer le cookie de session Laravel
        $sessionCookie = config('session.cookie');
        cookie()->queue(cookie()->forget($sessionCookie));
        
        // Supprimer le cookie "remember me"
        $rememberCookie = Auth::getRecallerName();
        cookie()->queue(cookie()->forget($rememberCookie));
    }
}