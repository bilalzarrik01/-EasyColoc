<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColocationRequest;
use App\Http\Requests\UpdateColocationRequest;
use App\Models\Colocation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ColocationController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $activeColocations = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('colocations.status', 'active')
            ->withPivot(['role', 'joined_at'])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('colocations.index', [
            'activeColocations' => $activeColocations,
        ]);
    }

    public function create(): View
    {
        return view('colocations.create');
    }

    public function store(StoreColocationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasActiveColocation()) {
            return back()
                ->withErrors([
                    'name' => 'You already belong to an active colocation.',
                ])
                ->withInput();
        }

        $colocation = DB::transaction(function () use ($request, $user): Colocation {
            $colocation = Colocation::create([
                'name' => $request->validated('name'),
                'owner_id' => $user->id,
                'status' => 'active',
            ]);

            $colocation->members()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
                'left_at' => null,
            ]);

            return $colocation;
        });

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation created successfully.');
    }

    public function show(Colocation $colocation): View
    {
        $this->abortUnlessActiveMember(auth()->user(), $colocation);

        $colocation->load([
            'activeMembers:id,name,email,reputation',
            'owner:id,name,email',
        ]);

        return view('colocations.show', [
            'colocation' => $colocation,
        ]);
    }

    public function edit(Colocation $colocation): View
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        return view('colocations.edit', [
            'colocation' => $colocation,
        ]);
    }

    public function update(UpdateColocationRequest $request, Colocation $colocation): RedirectResponse
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        $colocation->update([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation updated successfully.');
    }

    public function cancel(Colocation $colocation): RedirectResponse
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        $colocation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation cancelled.');
    }

    public function leave(Colocation $colocation): RedirectResponse
    {
        $user = auth()->user();
        $this->abortUnlessActiveMember($user, $colocation);

        if ($colocation->owner_id === $user->id) {
            return back()->withErrors([
                'membership' => 'Owner cannot leave the colocation. Cancel it instead.',
            ]);
        }

        $colocation->members()
            ->updateExistingPivot($user->id, [
                'left_at' => now(),
            ]);

        return redirect()
            ->route('colocations.index')
            ->with('status', 'You left the colocation.');
    }

    private function abortUnlessActiveMember(User $user, Colocation $colocation): void
    {
        $isMember = $colocation->members()
            ->where('users.id', $user->id)
            ->wherePivotNull('left_at')
            ->exists();

        abort_unless($isMember, 403);
    }

    private function abortUnlessOwner(User $user, Colocation $colocation): void
    {
        abort_unless($colocation->owner_id === $user->id, 403);
    }
}
