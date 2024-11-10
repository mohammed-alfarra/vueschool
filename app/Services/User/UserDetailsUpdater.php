<?php

namespace App\Services\User;

use App\Models\User;
use Faker\Generator as Faker;

class UserDetailsUpdater
{
    public function __construct(private Faker $faker) {}

    public function update(User $user): void
    {
        $user->update([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'timezone' => collect(config('timezones.supported'))->random(),
        ]);
    }
}
