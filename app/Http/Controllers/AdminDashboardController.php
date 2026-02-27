<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users_total' => User::query()->count(),
            'users_active' => User::query()
                ->where('is_active', true)
                ->where('is_banned', false)
                ->count(),
            'users_banned' => User::query()->where('is_banned', true)->count(),
            'users_inactive' => User::query()->where('is_active', false)->count(),
            'colocations_total' => Colocation::query()->count(),
            'colocations_active' => Colocation::query()->where('status', 'active')->count(),
            'colocations_cancelled' => Colocation::query()->where('status', 'cancelled')->count(),
            'expenses_total' => Expense::query()->count(),
            'expenses_amount_total' => (float) Expense::query()->sum('amount'),
        ];

        $users = User::query()
            ->latest('id')
            ->paginate(20);

        return view('admin.dashboard', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->ensureDifferentUser($request->user(), $user);

        $user->update([
            'is_banned' => true,
            'is_active' => false,
        ]);

        return back()->with('status', 'User banned successfully.');
    }

    public function unban(Request $request, User $user): RedirectResponse
    {
        $this->ensureDifferentUser($request->user(), $user);

        $user->update([
            'is_banned' => false,
            'is_active' => true,
        ]);

        return back()->with('status', 'User unbanned successfully.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->ensureDifferentUser($request->user(), $user);

        $user->update([
            'is_active' => false,
        ]);

        return back()->with('status', 'User deactivated successfully.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->ensureDifferentUser($request->user(), $user);

        $user->update([
            'is_active' => true,
        ]);

        return back()->with('status', 'User activated successfully.');
    }

    private function ensureDifferentUser(?User $actor, User $target): void
    {
        abort_unless($actor !== null, 403);

        if ((int) $actor->id === (int) $target->id) {
            abort(422, 'You cannot perform this action on your own account.');
        }
    }
}

