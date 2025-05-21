<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Constructor to apply middleware
     */
    public function __construct()
    {
        // Prevent logged-in users from accessing login page
        $this->middleware('guest')->only('create', 'store');
        
        // Only authenticated users can logout
        $this->middleware('auth')->only('destroy');
    }

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // If somehow a logged-in user gets here (should be prevented by middleware)
        if (Auth::check()) {
            // Check if user is admin and redirect appropriately
            if (Auth::user()->is_admin == 1) {
                return redirect()->route('admin.dashboard');
            }
            
            // For regular users, redirect to dashboard
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user is admin and redirect appropriately
        if (Auth::user()->is_admin == 1) {
            return redirect()->intended(route('admin.dashboard'));
        }
        
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Store the user type before logout
        $isAdmin = Auth::user() && Auth::user()->is_admin == 1;
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Clear the intended URL so the user isn't redirected back to protected pages after logout
        $request->session()->forget('url.intended');

        // Redirect based on user type
        if ($isAdmin) {
            return redirect()->route('admin.login');
        }
        
        return redirect()->route('home');
    }
}