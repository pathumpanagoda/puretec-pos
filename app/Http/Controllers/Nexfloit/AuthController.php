<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        // Redirect if already logged in
        if (session()->has('nexfloit_user_id')) {
            return redirect()->route('nexfloit.dashboard');
        }

        return view('nexfloit.auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find platform user by email
        $user = PlatformUser::where('email', $request->email)->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account is inactive. Please contact support.');
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Store user ID in session
        session(['nexfloit_user_id' => $user->id]);

        return redirect()->route('nexfloit.dashboard')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        session()->forget('nexfloit_user_id');

        return redirect()->route('nexfloit.login')
            ->with('success', 'You have been logged out.');
    }
}
