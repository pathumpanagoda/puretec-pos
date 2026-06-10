<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Show forgot password form.
     */
    public function showForgotForm()
    {
        return view('nexfloit.auth.forgot-password');
    }

    /**
     * Find user by email or username.
     */
    public function findUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = $request->identifier;

        // Find by email or username
        $user = PlatformUser::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return back()->withErrors(['identifier' => 'No account found with that email or username.']);
        }

        // Generate a temporary token
        $token = Str::random(64);

        // Store token
        DB::table('platform_password_resets')->insert([
            'identifier' => $identifier,
            'token'      => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        // Check if user has security question
        if ($user->security_question && $user->security_answer) {
            return redirect()->route('nexfloit.security-question', [
                'token' => $token,
                'identifier' => $identifier,
            ]);
        }

        // No security question - go directly to reset
        return redirect()->route('nexfloit.reset-password.form', [
            'token' => $token,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Show security question form.
     */
    public function showSecurityQuestion(Request $request)
    {
        $identifier = $request->identifier;
        $token = $request->token;

        $user = PlatformUser::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user || !$user->security_question) {
            return redirect()->route('nexfloit.forgot-password')->withErrors(['identifier' => 'Invalid request.']);
        }

        return view('nexfloit.auth.security-question', [
            'user'       => $user,
            'token'      => $token,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Verify security answer.
     */
    public function verifySecurityAnswer(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'token'      => 'required|string',
            'answer'     => 'required|string',
        ]);

        $user = PlatformUser::where('email', $request->identifier)
            ->orWhere('username', $request->identifier)
            ->first();

        if (!$user) {
            return back()->withErrors(['answer' => 'Invalid request.']);
        }

        // Check answer (case-insensitive)
        if (strtolower(trim($user->security_answer)) !== strtolower(trim($request->answer))) {
            return back()->withErrors(['answer' => 'Incorrect answer. Please try again.']);
        }

        return redirect()->route('nexfloit.reset-password.form', [
            'token'      => $request->token,
            'identifier' => $request->identifier,
            'verified'   => 1,
        ]);
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(Request $request)
    {
        $identifier = $request->identifier;
        $token = $request->token;

        // Verify token exists and not expired
        $reset = DB::table('platform_password_resets')
            ->where('identifier', $identifier)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$reset || !Hash::check($token, $reset->token)) {
            return redirect()->route('nexfloit.forgot-password')
                ->withErrors(['identifier' => 'Invalid or expired reset link. Please try again.']);
        }

        $user = PlatformUser::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return redirect()->route('nexfloit.forgot-password')
                ->withErrors(['identifier' => 'User not found.']);
        }

        return view('nexfloit.auth.reset-password', [
            'user'       => $user,
            'token'      => $token,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier'            => 'required|string',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        // Verify token
        $reset = DB::table('platform_password_resets')
            ->where('identifier', $request->identifier)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return redirect()->route('nexfloit.forgot-password')
                ->withErrors(['identifier' => 'Invalid or expired reset link. Please try again.']);
        }

        $user = PlatformUser::where('email', $request->identifier)
            ->orWhere('username', $request->identifier)
            ->first();

        if (!$user) {
            return redirect()->route('nexfloit.forgot-password')
                ->withErrors(['identifier' => 'User not found.']);
        }

        // Update password
        $user->update([
            'password'          => $request->password,
            'password_reset_at' => now(),
        ]);

        // Delete used tokens
        DB::table('platform_password_resets')
            ->where('identifier', $request->identifier)
            ->delete();

        return redirect()->route('nexfloit.login')
            ->with('success', 'Password reset successfully! You can now login with your new password.');
    }
}
