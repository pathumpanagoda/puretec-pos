<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller
{
    /**
     * Display the onboarding setup form.
     */
    public function index()
    {
        $store = Store::first();
        if ($store && isset($store->settings['onboarding_completed']) && $store->settings['onboarding_completed'] === true) {
            return redirect()->route('login');
        }

        return view('auth.setup', compact('store'));
    }

    /**
     * Save the client onboarding details and mark setup as completed.
     */
    public function store(Request $request)
    {
        $store = Store::first();
        if ($store && isset($store->settings['onboarding_completed']) && $store->settings['onboarding_completed'] === true) {
            return redirect()->route('login');
        }

        // Find the default super admin user (or fallback to any super admin or create one)
        $user = User::where('role', 'super_admin')->first();
        $userId = $user ? $user->id : null;

        $request->validate([
            'store_name' => 'required|string|max:100',
            'store_phone' => 'nullable|string|max:20',
            'store_address' => 'nullable|string|max:255',
            'username' => 'required|string|min:4|max:50|unique:users,username,' . $userId,
            'email' => 'required|email|max:100|unique:users,email,' . $userId,
            'password' => 'required|string|min:6|confirmed',
        ]);
        if (!$user) {
            $user = new User();
            $user->role = 'super_admin';
            $user->store_id = $store ? $store->id : 1;
            $user->is_active = true;
        }

        $user->name = 'Owner';
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($store) {
            // Update Store
            $settings = $store->settings ?? [];
            $settings['onboarding_completed'] = true;

            $store->update([
                'name' => $request->store_name,
                'phone' => $request->store_phone ?? $store->phone,
                'address' => $request->store_address ?? $store->address,
                'settings' => $settings,
            ]);
        }

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Onboarding completed! Welcome to ' . ($store ? $store->name : 'Pure POS'));
    }
}
