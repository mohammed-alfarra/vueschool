<?php

namespace Tests\Unit\Services\User;

use App\Models\User;
use App\Services\User\UserBatchUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessUserBatchUpdatesTest extends TestCase
{
    use RefreshDatabase;

    public function testProcessAndLogUpdatesWithChangedAttributes(): void
    {
        $user = User::factory()->create([
            'is_synced' => false,
            'first_name' => 'John',
            'timezone' => 'UTC',
        ]);

        // Simulate changes to the user attributes
        $user->first_name = 'Samantha';
        $user->timezone = 'America/Los_Angeles';

        $batchUpdater = new UserBatchUpdater();

        $batchUpdater->processAndLogUpdates(collect([$user]));
        // dd(User::find($user->id));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_synced' => true,
        ]);
    }
}
