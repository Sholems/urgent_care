<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\ExchangeRate;

class FetchExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch USD to SLL exchange rate from Open Exchange Rates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $appId = env('OPEN_EXCHANGE_RATES_APP_ID');

        if (!$appId) {
            $this->error('OPEN_EXCHANGE_RATES_APP_ID is not set.');
            return self::FAILURE;
        }

        $response = Http::get('https://openexchangerates.org/api/latest.json', [
            'app_id' => $appId,
            'symbols' => 'SLL',
        ]);

        if ($response->failed()) {
            $this->error('Failed to fetch exchange rate.');
            return self::FAILURE;
        }

        $rate = data_get($response->json(), 'rates.SLL');

        if (!$rate) {
            $this->error('SLL rate not found in response.');
            return self::FAILURE;
        }

        ExchangeRate::create([
            'usd_to_sll' => $rate,
        ]);

        $this->info('Stored exchange rate: 1 USD = ' . $rate . ' SLL');

        return self::SUCCESS;
    }
}
