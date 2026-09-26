<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetRequestController extends Controller
{
    /**
     * Show all password reset requests for the store admin.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $storeId = $user->store_id;

        $query = PasswordResetRequest::with('user')
            ->where('store_id', $storeId)
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);
        $pendingCount = PasswordResetRequest::where('store_id', $storeId)->pending()->count();

        return view('admin.password-reset-requests', compact('requests', 'pendingCount'));
    }

    /**
     * Approve a password reset request.
     */
    public function approve($id)
    {
        $user = Auth::user();
        $resetRequest = PasswordResetRequest::where('store_id', $user->store_id)
            ->findOrFail($id);

        if (!$resetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        // Generate a new token for the user to use
        $token = Str::random(64);

        $resetRequest->update([
            'status'           => 'approved',
            'token'            => Hash::make($token),
            'approved_by_type' => 'store_admin',
            'approved_by_id'   => $user->id,
            'approved_at'      => now(),
            'expires_at'       => now()->addHours(1),
        ]);

        // Build the reset URL for display
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $resetRequest->user->email,
        ]);

        return back()->with('success', 'Password reset request approved! The user can now reset their password.')
                      ->with('reset_url', $resetUrl)
                      ->with('approved_user', $resetRequest->user->name);
    }

    /**
     * Reject a password reset request.
     */
    public function reject($id)
    {
        $user = Auth::user();
        $resetRequest = PasswordResetRequest::where('store_id', $user->store_id)
            ->findOrFail($id);

        if (!$resetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $resetRequest->update([
            'status'           => 'rejected',
            'approved_by_type' => 'store_admin',
            'approved_by_id'   => $user->id,
            'rejected_at'      => now(),
        ]);

        return back()->with('success', 'Password reset request has been rejected.');
    }
}
