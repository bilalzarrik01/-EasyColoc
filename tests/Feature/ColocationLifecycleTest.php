<?php

namespace Tests\Feature;

use App\Models\Colocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColocationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_remove_member_with_debt_transfers_debt_to_owner_and_penalizes_member(): void
    {
        $owner = User::factory()->create();
        $memberToRemove = User::factory()->create();
        $creditor = User::factory()->create();

        $colocation = $this->createColocation($owner, [$memberToRemove, $creditor]);

        $this->actingAs($owner)
            ->post(route('expenses.store', $colocation), [
                'title' => 'Monthly bill',
                'amount' => 30.00,
                'expense_date' => now()->toDateString(),
                'payer_id' => $creditor->id,
                'category_id' => null,
            ]);

        $response = $this->actingAs($owner)
            ->patch(route('colocations.removeMember', [$colocation, $memberToRemove]));

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('colocation_user', [
            'colocation_id' => $colocation->id,
            'user_id' => $memberToRemove->id,
            'left_at' => null,
        ]);

        $memberToRemove->refresh();
        $this->assertSame(-1, $memberToRemove->reputation);

        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $memberToRemove->id,
            'colocation_id' => $colocation->id,
            'delta' => -1,
            'reason' => 'removed_with_debt',
        ]);

        $this->assertDatabaseHas('settlements', [
            'colocation_id' => $colocation->id,
            'debtor_id' => $owner->id,
            'creditor_id' => $creditor->id,
            'status' => 'pending',
            'amount' => '20.00',
        ]);
    }

    public function test_member_leave_without_debt_gives_positive_reputation(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $colocation = $this->createColocation($owner, [$member]);

        $response = $this->actingAs($member)
            ->patch(route('colocations.leave', $colocation));

        $response->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame(1, $member->reputation);

        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $member->id,
            'colocation_id' => $colocation->id,
            'delta' => 1,
            'reason' => 'left_without_debt',
        ]);
    }

    public function test_cancel_colocation_updates_member_reputations(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $colocation = $this->createColocation($owner, [$member]);

        $response = $this->actingAs($owner)
            ->patch(route('colocations.cancel', $colocation));

        $response->assertSessionHasNoErrors();

        $colocation->refresh();
        $owner->refresh();
        $member->refresh();

        $this->assertSame('cancelled', $colocation->status);
        $this->assertSame(1, $owner->reputation);
        $this->assertSame(1, $member->reputation);

        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $owner->id,
            'colocation_id' => $colocation->id,
            'delta' => 1,
            'reason' => 'cancelled_without_debt',
        ]);

        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $member->id,
            'colocation_id' => $colocation->id,
            'delta' => 1,
            'reason' => 'cancelled_without_debt',
        ]);
    }

    private function createColocation(User $owner, array $members): Colocation
    {
        $colocation = Colocation::create([
            'name' => 'Lifecycle Home',
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);

        $colocation->members()->attach($owner->id, [
            'role' => 'owner',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        foreach ($members as $member) {
            $colocation->members()->attach($member->id, [
                'role' => 'member',
                'joined_at' => now(),
                'left_at' => null,
            ]);
        }

        return $colocation;
    }
}
