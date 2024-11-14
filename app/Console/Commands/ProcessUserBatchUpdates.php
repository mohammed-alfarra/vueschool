<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\User\UserBatchUpdater;
use Illuminate\Console\Command;

class ProcessUserBatchUpdates extends Command
{
    protected $signature = 'users:process-updates 
                            {--batch-size=1000 : Number of users to process per batch}';

    protected $description = 'Process and log batch updates for user attribute changes';

    protected UserBatchUpdater $userBatchUpdater;

    public function __construct(UserBatchUpdater $userBatchUpdater)
    {
        parent::__construct();
        $this->userBatchUpdater = $userBatchUpdater;
    }

    public function handle()
    {
        $this->info("Processing user updates...");

        $batchSize = (int) $this->option('batch-size');

        User::where('is_synced', false)
            ->chunkById($batchSize, function ($users) {
                $this->userBatchUpdater->processAndLogUpdates($users);
            });

        $this->info("User updates processed and logged successfully.");
    }
}
