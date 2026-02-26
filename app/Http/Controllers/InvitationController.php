<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class InvitationController extends Controller
{
    public function store(Request $request, Colocation $colocation): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($colocation->owner_id === $user->id, 403);

        if ($colocation->status !== 'active') {
            return back()->withErrors([
                'invitation' => 'You can only invite members to an active colocation.',
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $alreadyMember = $colocation->activeMembers()
            ->where('users.email', $email)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors([
                'invitation' => 'This user is already an active member of the colocation.',
            ]);
        }

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $email,
            'token' => Str::random(64),
            'status' => 'pending',
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $link = route('invitations.show', $invitation->token);

        try {
            $html = view('emails.invitation', [
                'inviterName' => $user->name,
                'colocationName' => $colocation->name,
                'invitationUrl' => $link,
            ])->render();

            Mail::html($html, function ($message) use ($email): void {
                $message->to($email)->subject('EasyColoc invitation');
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'invitation' => 'Invitation created but email sending failed. Check SMTP settings.',
            ]);
        }

        return back()->with('status', 'Invitation sent successfully.');
    }

    public function show(Request $request, string $token): View
    {
        /** @var User $user */
        $user = $request->user();
        $invitation = $this->findInvitationOrFail($token);
        $this->expireIfNeeded($invitation);

        $invitation->load([
            'colocation:id,name,status,owner_id',
            'inviter:id,name,email',
        ]);

        $isInvitedUser = strcasecmp($user->email, $invitation->email) === 0;
        $isColocationOwner = $invitation->colocation->owner_id === $user->id;

        abort_unless($isInvitedUser || $isColocationOwner, 403);

        return view('invitations.show', [
            'invitation' => $invitation,
            'canRespond' => $isInvitedUser,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $invitation = $this->findInvitationOrFail($token);
        $this->expireIfNeeded($invitation);

        abort_unless(strcasecmp($user->email, $invitation->email) === 0, 403);

        if ($invitation->status !== 'pending') {
            return redirect()
                ->route('invitations.show', $invitation->token)
                ->withErrors([
                    'invitation' => 'This invitation is no longer pending.',
                ]);
        }

        $invitation->loadMissing('colocation');

        if ($invitation->colocation->status !== 'active') {
            return redirect()
                ->route('invitations.show', $invitation->token)
                ->withErrors([
                    'invitation' => 'This colocation is no longer active.',
                ]);
        }

        $alreadyActiveMember = $invitation->colocation->members()
            ->where('users.id', $user->id)
            ->wherePivotNull('left_at')
            ->exists();

        if (! $alreadyActiveMember && $user->hasActiveColocation()) {
            return redirect()
                ->route('invitations.show', $invitation->token)
                ->withErrors([
                    'invitation' => 'You already belong to an active colocation.',
                ]);
        }

        DB::transaction(function () use ($invitation, $user): void {
            $existingMembership = $invitation->colocation->members()
                ->where('users.id', $user->id)
                ->exists();

            if ($existingMembership) {
                $invitation->colocation->members()
                    ->updateExistingPivot($user->id, [
                        'role' => 'member',
                        'joined_at' => now(),
                        'left_at' => null,
                    ]);
            } else {
                $invitation->colocation->members()
                    ->attach($user->id, [
                        'role' => 'member',
                        'joined_at' => now(),
                        'left_at' => null,
                    ]);
            }

            $invitation->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);
        });

        return redirect()
            ->route('colocations.show', $invitation->colocation)
            ->with('status', 'Invitation accepted. Welcome!');
    }

    public function refuse(Request $request, string $token): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $invitation = $this->findInvitationOrFail($token);
        $this->expireIfNeeded($invitation);

        abort_unless(strcasecmp($user->email, $invitation->email) === 0, 403);

        if ($invitation->status !== 'pending') {
            return redirect()
                ->route('invitations.show', $invitation->token)
                ->withErrors([
                    'invitation' => 'This invitation is no longer pending.',
                ]);
        }

        $invitation->update([
            'status' => 'refused',
            'responded_at' => now(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Invitation refused.');
    }

    private function findInvitationOrFail(string $token): Invitation
    {
        return Invitation::query()
            ->where('token', $token)
            ->firstOrFail();
    }

    private function expireIfNeeded(Invitation $invitation): void
    {
        if ($invitation->status !== 'pending') {
            return;
        }

        if ($invitation->expires_at === null || ! $invitation->expires_at->isPast()) {
            return;
        }

        $invitation->update([
            'status' => 'expired',
            'responded_at' => now(),
        ]);
    }
}
