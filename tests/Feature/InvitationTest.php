<?php

namespace Tests\Feature;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_invitation(): void
    {
        Mail::fake();

        $owner = User::factory()->create();
        $colocation = $this->createActiveColocation($owner, 'Team House');

        $response = $this->actingAs($owner)->post(route('invitations.store', $colocation), [
            'email' => 'Invitee@Example.com',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invitations', [
            'colocation_id' => $colocation->id,
            'email' => 'invitee@example.com',
            'status' => 'pending',
            'invited_by' => $owner->id,
        ]);

        Mail::assertSentCount(1);
    }

    public function test_invited_user_can_accept_invitation(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $colocation = $this->createActiveColocation($owner, 'Blue Flat');

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $invitee->email,
            'token' => 'token-accept-123',
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($invitee)
            ->patch(route('invitations.accept', $invitation->token));

        $response->assertRedirect(route('colocations.show', $colocation));

        $this->assertDatabaseHas('colocation_user', [
            'colocation_id' => $colocation->id,
            'user_id' => $invitee->id,
            'role' => 'member',
            'left_at' => null,
        ]);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);
    }

    public function test_user_with_active_colocation_cannot_accept_another_invitation(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $this->createActiveColocation($invitee, 'Current Home');
        $targetColocation = $this->createActiveColocation($owner, 'Other Home');

        $invitation = Invitation::create([
            'colocation_id' => $targetColocation->id,
            'email' => $invitee->email,
            'token' => 'token-blocked-456',
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($invitee)
            ->patch(route('invitations.accept', $invitation->token));

        $response->assertRedirect(route('invitations.show', $invitation->token));
        $response->assertSessionHasErrors('invitation');

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('colocation_user', [
            'colocation_id' => $targetColocation->id,
            'user_id' => $invitee->id,
            'left_at' => null,
        ]);
    }

    public function test_other_user_cannot_open_someone_else_invitation(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);
        $otherUser = User::factory()->create();

        $colocation = $this->createActiveColocation($owner, 'Sharing Place');

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $invitee->email,
            'token' => 'token-private-789',
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($otherUser)
            ->get(route('invitations.show', $invitation->token))
            ->assertForbidden();
    }

    public function test_owner_can_open_invitation_page(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $colocation = $this->createActiveColocation($owner, 'Owner House');

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $invitee->email,
            'token' => 'token-owner-view-999',
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($owner)
            ->get(route('invitations.show', $invitation->token))
            ->assertOk();
    }

    private function createActiveColocation(User $owner, string $name): Colocation
    {
        $colocation = Colocation::create([
            'name' => $name,
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);

        $colocation->members()->attach($owner->id, [
            'role' => 'owner',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        return $colocation;
    }
}
