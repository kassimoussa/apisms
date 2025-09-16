<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;
use Illuminate\Support\Facades\Session;

class AuthenticateWebClient
{
    /**
     * Handle an incoming request for web client authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if client is authenticated in session
        $clientId = Session::get('client_id');
        
        if (!$clientId) {
            return redirect()->route('login')
                ->with('error', 'Please login to access the client portal.');
        }

        // Find the client
        $client = Client::find($clientId);
        
        if (!$client || !$client->isActive()) {
            Session::forget('client_id');
            return redirect()->route('login')
                ->with('error', 'Your account is inactive or not found.');
        }

        // Add client to request for use in controllers
        $request->attributes->set('client', $client);
        
        return $next($request);
    }
}