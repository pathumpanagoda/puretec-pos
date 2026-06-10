<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Find user by email or username and redirect to reset form.
     */
    public function findUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = $request->identifier;

        // Find user by email or username
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return back()->with('error', 'No account found with that email or username.');
        }

        // Generate a temporary token
        $token = Str::random(64);

        // Delete old tokens for this user
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Check if user has security question set
        if (!empty($user->security_question) && !empty($user->security_answer)) {
            return redirect()->route('password.security-question', [
                'token' => $token,
                'email' => $user->email,
            ]);
        }

        // No security question - go directly to reset form
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }

    /**
     * Show security question form.
     */
    public function showSecurityQuestion(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || empty($user->security_question)) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid request.');
        }

        return view('auth.security-question', [
            'token' => $request->token,
            'email' => $request->email,
            'question' => $user->security_question,
            'user_name' => $user->name,
        ]);
    }

    /**
     * Verify security answer.
     */
    public function verifySecurityAnswer(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'answer' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        // Verify the answer (case-insensitive)
        if (strtolower(trim($request->answer)) !== strtolower(trim($user->security_answer))) {
            return back()->with('error', 'Incorrect answer. Please try again.');
        }

        // Answer correct - redirect to reset form
        return redirect()->route('password.reset', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(Request $request, $token)
    {
        // Verify token exists and is valid
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset link. Please try again.');
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('password.request')
                ->with('error', 'Reset link has expired. Please try again.');
        }

        $user = User::where('email', $request->email)->first();

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
            'user_name' => $user ? $user->name : '',
        ]);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Find the reset token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        // Check if token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->with('error', 'Reset link has expired. Please request a new one.');
        }

        // Update the password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password has been reset successfully! Please login with your new password.');
    }
}
