<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait EmployeeSecurity
{
    /**
     * Vérifier si l'utilisateur connecté est un employé
     */
    protected function checkEmployeeAccess()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'employee') {
            return $this->redirectByRole($user);
        }
        
        return null; // Accès autorisé
    }
    
    /**
     * Redirection selon le rôle
     */
    private function redirectByRole($user)
    {
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter');
        }
        
        switch ($user->role) {
            case 'hr_admin':
                return redirect()->route('hr.dashboard')
                               ->with('error', 'Accès refusé - Section employés uniquement');
            case 'department_head':
                return redirect()->route('dashboard')
                               ->with('error', 'Accès refusé - Section employés uniquement');
            default:
                return redirect()->route('login')
                               ->with('error', 'Rôle non reconnu');
        }
    }
}