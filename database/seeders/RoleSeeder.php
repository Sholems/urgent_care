<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'administrator'],
            [
                'name' => 'Administrator',
                'permissions' => [
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                    'platform.appointments' => true,
                ],
            ]
        );
    }
}

