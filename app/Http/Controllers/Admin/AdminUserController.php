<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // Show all users (excluding admins), with search + filter
    public function index(Request $request)
    {
        $query = User::withTrashed()
            ->where('role', '!=', 'admin')
            ->latest();

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filter === 'verified') {
            $query->whereNotNull('email_verified_at')->whereNull('deleted_at');
        } elseif ($request->filter === 'unverified') {
            $query->whereNull('email_verified_at')->whereNull('deleted_at');
        } elseif ($request->filter === 'deactivated') {
            $query->onlyTrashed();
        } else {
            // Default: show only active users
            $query->whereNull('deleted_at');
        }

        $users = $query->paginate(10)->withQueryString();

        return view('user.index', compact('users'));
    }

    // Manually verify a user's email
    public function verify(User $user)
    {
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return back()->with('status', "{$user->name}'s email has been verified.");
    }

    // Soft delete (deactivate) a user
    public function deactivate(User $user)
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Cannot deactivate an admin account.']);
        }

        $user->delete(); // soft delete — sets deleted_at

        return back()->with('status', "{$user->name} has been deactivated.");
    }

    // Restore a soft-deleted user
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore(); // clears deleted_at

        return back()->with('status', "{$user->name} has been reactivated.");
    }
}