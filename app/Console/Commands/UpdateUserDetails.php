<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\User\UserDetailsUpdater;
use Illuminate\Console\Command;

class UpdateUserDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-details {--chunk=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update users\' first name, last name, and timezone with random values';

    public function __construct(private UserDetailsUpdater $updater)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $chunkSize = $this->option('chunk');

        User::chunk($chunkSize, function ($users) {
            $users->each(function (User $user) {
                $this->updater->update($user);
            });

            $this->info(count($users) . ' users updated.');
        });

        $this->info('User details updated successfully.');
    }
}
