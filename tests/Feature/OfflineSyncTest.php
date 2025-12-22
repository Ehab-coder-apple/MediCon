<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test branch
        $this->branch = Branch::create([
            'name' => 'Test Pharmacy',
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'radius' => 100,
        ]);

        // Create test user
        $this->user = User::create([
            'name' => 'Test Pharmacist',
            'email' => 'test@medicon.com',
            'password' => bcrypt('password'),
        ]);

        // Assign branch to user
        $this->user->branches()->attach($this->branch->id);

        // Get auth token
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_check_in_endpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/check-in', [
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'branch_id' => $this->branch->id,
            'device_info' => 'Test Device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'check_in_time']);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_check_out_endpoint()
    {
        // First check in
        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/check-in', [
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'branch_id' => $this->branch->id,
            'device_info' => 'Test Device',
        ]);

        // Then check out
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/check-out', [
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'branch_id' => $this->branch->id,
            'device_info' => 'Test Device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'check_out_time', 'total_hours']);
    }

    public function test_break_start_endpoint()
    {
        // First check in
        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/check-in', [
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'branch_id' => $this->branch->id,
            'device_info' => 'Test Device',
        ]);

        // Start break
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/break-start', [
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'break_start_time']);
    }

    public function test_break_end_endpoint()
    {
        // Check in
        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/check-in', [
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'branch_id' => $this->branch->id,
            'device_info' => 'Test Device',
        ]);

        // Start break
        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/break-start', [
            'branch_id' => $this->branch->id,
        ]);

        // End break
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/attendance/break-end', [
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'break_end_time', 'break_duration_minutes']);
    }
}

