<?php

namespace Tests\Feature;

use App\Models\Colocation;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_creation_generates_equal_shares_and_pending_settlement(): void
    {
        [$owner, $member, $colocation] = $this->createColocationWithTwoMembers();

        $response = $this->actingAs($owner)
            ->post(route('expenses.store', $colocation), [
                'title' => 'Groceries',
                'amount' => 10.00,
                'expense_date' => now()->toDateString(),
                'payer_id' => $owner->id,
                'category_id' => null,
            ]);

        $response->assertSessionHasNoErrors();

        $expenseId = (int) $colocation->expenses()->firstOrFail()->id;

        $this->assertDatabaseHas('expense_shares', [
            'expense_id' => $expenseId,
            'user_id' => $owner->id,
            'share_amount' => '5.00',
        ]);

        $this->assertDatabaseHas('expense_shares', [
            'expense_id' => $expenseId,
            'user_id' => $member->id,
            'share_amount' => '5.00',
        ]);

        $this->assertDatabaseHas('settlements', [
            'colocation_id' => $colocation->id,
            'debtor_id' => $member->id,
            'creditor_id' => $owner->id,
            'amount' => '5.00',
            'status' => 'pending',
        ]);
    }

    public function test_mark_paid_changes_status_and_clears_pending_settlements(): void
    {
        [$owner, $member, $colocation] = $this->createColocationWithTwoMembers();

        $this->actingAs($owner)
            ->post(route('expenses.store', $colocation), [
                'title' => 'Internet',
                'amount' => 20.00,
                'expense_date' => now()->toDateString(),
                'payer_id' => $owner->id,
                'category_id' => null,
            ]);

        $settlement = Settlement::query()
            ->where('colocation_id', $colocation->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $response = $this->actingAs($member)
            ->patch(route('settlements.markPaid', [$colocation, $settlement]));

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settlements', [
            'id' => $settlement->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseMissing('settlements', [
            'colocation_id' => $colocation->id,
            'status' => 'pending',
        ]);
    }

    private function createColocationWithTwoMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $colocation = Colocation::create([
            'name' => 'Finance Flat',
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);

        $colocation->members()->attach($owner->id, [
            'role' => 'owner',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        $colocation->members()->attach($member->id, [
            'role' => 'member',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        return [$owner, $member, $colocation];
    }
}
