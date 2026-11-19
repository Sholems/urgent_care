<?php

namespace Database\Seeders;

use App\Models\MonimeCredential;
use Illuminate\Database\Seeder;

class MonimeCredentialSeeder extends Seeder
{
    /**
     * Seed primary Monime API credentials for checkout/payments.
     */
    public function run(): void
    {
        MonimeCredential::query()->updateOrCreate(
            ['space_id' => 'spc-k6Qnn9Sfak3pyZJNo19gxHfumq9'],
            [
                'name' => 'Primary Monime',
                'api_token' => 'mon_U31JUFWdee4fIyovun3QoeBago7y9V93X2CpUKnFZ7BCKIfHYjDnAoSGureTChVa',
                'api_version' => 'caph.2025-08-23',
                'enabled' => true,
            ]
        );
    }
}
