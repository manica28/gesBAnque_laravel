<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->state(['type_user' => 'admin'])->create();

        return [
            'id_user' => (string) $user->id_user,
            'permissions' => ['manage_users', 'manage_accounts', 'view_reports'],
        ];
    }
}
