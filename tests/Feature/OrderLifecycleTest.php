<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Device;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_order_can_be_claimed_marked_ready_paid_and_reviewed(): void
    {
        Mail::fake();

        $client = $this->user(User::ROLE_CLIENT, 'client@example.test');
        $staff = $this->user(User::ROLE_STAFF, 'staff@example.test');
        $this->user(User::ROLE_ADMIN, 'admin@example.test');

        $branch = Branch::create([
            'name' => 'DeviceLab Centrs',
            'address' => 'Riga',
            'is_active' => true,
        ]);

        $device = Device::create([
            'user_id' => $client->id,
            'type' => 'phone',
            'brand' => 'Samsung',
            'model' => 'Galaxy S24',
        ]);

        DB::table('services')->insert([
            'name' => 'Diagnostika',
            'description' => 'Test diagnostics',
            'base_price' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $createResponse = $this->actingAs($client)->postJson('/api/my/orders', [
            'branch_id' => $branch->id,
            'device_id' => $device->id,
            'request_type' => 'general',
            'problem_description' => 'Device does not turn on.',
        ]);

        $createResponse
            ->assertOk()
            ->assertJsonPath('status', 'new')
            ->assertJsonPath('final_cost', 15);

        $orderId = $createResponse->json('id');

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'item_type' => 'service',
            'quantity' => 1,
        ]);

        $this->actingAs($staff)
            ->postJson("/api/staff/orders/{$orderId}/claim")
            ->assertOk()
            ->assertJsonPath('status', 'confirmed')
            ->assertJsonPath('assigned_staff_id', $staff->id);

        $this->actingAs($staff)
            ->patchJson("/api/staff/orders/{$orderId}", [
                'status' => 'ready',
                'status_comment' => 'Ierīce gatava saņemšanai.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ready');

        $this->actingAs($client)
            ->postJson("/api/my/orders/{$orderId}/pay")
            ->assertOk()
            ->assertJsonPath('status', 'done')
            ->assertJsonPath('payment.status', Payment::STATUS_PAID);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $orderId,
            'new_status' => 'done',
            'changed_by' => $client->id,
        ]);

        $this->actingAs($staff)
            ->getJson('/api/staff/orders/my')
            ->assertOk()
            ->assertJsonMissing(['id' => $orderId]);

        $this->actingAs($client)
            ->postJson("/api/my/orders/{$orderId}/review", [
                'rating' => 5,
                'comment' => 'Labs serviss.',
            ])
            ->assertCreated()
            ->assertJsonPath('rating', 5);

        $this->actingAs($client)
            ->postJson("/api/my/orders/{$orderId}/review", [
                'rating' => 4,
            ])
            ->assertStatus(409);
    }

    public function test_client_cannot_create_order_for_another_clients_device(): void
    {
        $client = $this->user(User::ROLE_CLIENT, 'client@example.test');
        $otherClient = $this->user(User::ROLE_CLIENT, 'other@example.test');

        $branch = Branch::create([
            'name' => 'DeviceLab Centrs',
            'address' => 'Riga',
            'is_active' => true,
        ]);

        $device = Device::create([
            'user_id' => $otherClient->id,
            'type' => 'phone',
            'brand' => 'Apple',
            'model' => 'iPhone 14',
        ]);

        DB::table('services')->insert([
            'name' => 'Diagnostika',
            'base_price' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($client)
            ->postJson('/api/my/orders', [
                'branch_id' => $branch->id,
                'device_id' => $device->id,
                'request_type' => 'general',
                'problem_description' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    private function user(string $role, string $email): User
    {
        return User::factory()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $email,
            'phone' => '+37120000000',
            'role' => $role,
            'is_blocked' => false,
        ]);
    }
}
