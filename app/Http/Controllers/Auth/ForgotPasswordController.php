<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Submit a password reset request (requires admin approval).
     */
    public function findUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'reason'     => 'nullable|string|max:500',
        ]);

        $identifier = $request->identifier;

        // Find user by email or username
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return back()->with('error', 'No account found with that email or username.');
        }

        // Check if there's already a pending request for this user
        $existingRequest = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('info', 'You already have a pending password reset request. Please wait for admin approval.');
        }

        // Generate a temporary token
        $token = Str::random(64);

        // Create the password reset request (pending approval)
        PasswordResetRequest::create([
            'user_id'  => $user->id,
            'store_id' => $user->store_id,
            'token'    => Hash::make($token),
            'status'   => 'pending',
            'reason'   => $request->reason,
        ]);

        return redirect()->route('login')->with('success', 'Your password reset request has been submitted successfully. An administrator will review your request and approve it shortly.');
    }

    /**
     * Show reset password form (only if approved by admin).
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid reset link. Please try again.');
        }

        // Find the approved reset request for this user
        $resetRequest = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$resetRequest) {
            return redirect()->route('password.request')
                ->with('error', 'No approved reset request found. Please submit a new request.');
        }

        // Verify token matches
        if (!Hash::check($token, $resetRequest->token)) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid reset link. Please submit a new request.');
        }

        // Check if token is expired
        if ($resetRequest->isExpired()) {
            $resetRequest->update(['status' => 'rejected']);
            return redirect()->route('password.request')
                ->with('error', 'Reset link has expired. Please submit a new request.');
        }

        return view('auth.reset-password', [
            'token'     => $token,
            'email'     => $email,
            'user_name' => $user->name,
        ]);
    }

    /**
     * Reset the password (only if request was approved).
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        // Find the approved reset request
        $resetRequest = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$resetRequest) {
            return back()->with('error', 'No approved reset request found.');
        }

        // Verify token
        if (!Hash::check($request->token, $resetRequest->token)) {
            return back()->with('error', 'Invalid reset link.');
        }

        // Check if expired
        if ($resetRequest->isExpired()) {
            $resetRequest->update(['status' => 'rejected']);
            return back()->with('error', 'Reset link has expired. Please submit a new request.');
        }

        // Update the password
        $user->update([
            'password'            => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        // Mark request as completed
        $resetRequest->update(['status' => 'completed']);

        return redirect()->route('login')->with('success', 'Password has been reset successfully! Please login with your new password.');
    }
}
