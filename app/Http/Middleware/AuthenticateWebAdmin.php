<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class AuthenticateWebAdmin
{
    /**
     * Handle an incoming request for admin web authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated in session
        $adminId = session('admin_id');
        
        if (!$adminId) {
            // Fallback: Check for old admin key system for backward compatibility
            $adminKey = config('app.admin_key', 'admin_secret_key_2024');
            if ($request->get('admin_key') === $adminKey) {
                // Create temporary session for old key system
                session(['admin_authenticated_legacy' => true]);
                return $next($request);
            }
            
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à l\'interface admin.');
        }

        // Find the admin
        $admin = Admin::find($adminId);
        
        if (!$admin || !$admin->isActive()) {
            session()->flush();
            return redirect()->route('login')
                ->with('error', 'Votre compte admin est inactif ou introuvable.');
        }

        // Add admin to request for use in controllers
        $request->attributes->set('admin', $admin);
        
        return $next($request);
    }
}
