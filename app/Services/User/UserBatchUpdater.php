<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserBatchUpdater
{
    private const BATCH_LIMIT = 1000;

    public function processAndLogUpdates(Collection $users): void
    {
        $batches = $this->batchUsers($users);

        foreach ($batches as $batchIndex => $batch) {
            $this->processBatch($batch, $batchIndex + 1);
        }
    }

    private function batchUsers(Collection $users): Collection
    {
        return $users->chunk(self::BATCH_LIMIT);
    }

    private function processBatch(Collection $batch, int $batchIndex): void
    {
        $payload = $this->prepareBatchPayload($batch);

        if (empty($payload['batches'][0]['subscribers'])) {
            return;
        }

        $this->logBatchUpdate($payload, $batchIndex);

        $this->markUsersAsSynced($batch);
    }

    private function prepareBatchPayload(Collection $batch): array
    {
        $payload = ['batches' => [['subscribers' => []]]];

        foreach ($batch as $user) {
            $changes = $this->getChangedAttributes($user);

            if ($changes) {
                $payload['batches'][0]['subscribers'][] = array_merge(['email' => $user->email], $changes);
            }
        }

        return $payload;
    }

    private function getChangedAttributes(User $user): array
    {
        $dirtyAttributes = $user->getDirty();
        $lastSyncedAttributes = $user->last_synced_attributes ?? [];
        $changes = [];

        foreach ($dirtyAttributes as $attribute => $value) {
            if (!isset($lastSyncedAttributes[$attribute]) || $lastSyncedAttributes[$attribute] !== $value) {
                $changes[$attribute] = $value;
            }
        }

        return $changes;
    }

    private function logBatchUpdate(array $payload, int $batchIndex): void
    {
        Log::info("Processing Batch {$batchIndex} - User Updates", ['payload' => $payload]);

        foreach ($payload['batches'][0]['subscribers'] as $subscriber) {
            $this->logUserUpdate($subscriber, $batchIndex);
        }
    }

    private function logUserUpdate(array $subscriber, int $batchIndex): void
    {
        $logMessage = sprintf('[User] email: %s, ', $subscriber['email']);

        foreach ($subscriber as $attribute => $value) {
            if ($attribute !== 'email') {
                $logMessage .= sprintf('%s: %s, ', $attribute, $value);
            }
        }

        $logMessage = rtrim($logMessage, ', ');

        Log::info("Batch {$batchIndex} - {$logMessage}");
    }

    private function markUsersAsSynced(Collection $batch): void
    {
        $emails = collect($batch)->pluck('email')->toArray();

        User::whereIn('email', $emails)->update(['is_synced' => true]);

        Log::info("Users marked as synced", ['emails' => $emails]);
    }
}
