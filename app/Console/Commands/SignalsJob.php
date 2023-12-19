<?php

namespace App\Console\Commands;

use App\Models\PostSignal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SignalsJob extends Command
{
    protected $signature = 'app:signals-job';
    protected $description = 'Command description';

    public function handle()
    {
        $signals = PostSignal::where('closed', 'no')->get();

        foreach ($signals as $signal) {

            $this->processSignal($signal);
        }
    }

    private function processSignal($signal)
    {
        $tp1 = $signal->profit_one;
        $tp2 = $signal->profit_two;
        $tp3 = $signal->profit_three;
        $stop_loss = $signal->stop_loss;
        $currency_pair = $signal->currency_pair;

        // Make API request
        $response = Http::get('https://api.twelvedata.com/time_series', [
            'symbol' => 'EUR/USD,GBP/USD,USD/JPY,USD/CAD,USD/CHF,AUD/USD',
            'interval' => '1min',
            'apikey' => 'acab338c6b924d6ebfa6183fc4a2491e',
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data[$currency_pair])) {
                $currencyData = $data[$currency_pair];
                $closePrice = $currencyData['values'][0]['close'] ?? 0;
                $this->updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss);
                $this->logApiResponse($currency_pair, $currencyData);
                $this->closeSignalIfTime($signal, $closePrice);
            }
        } else {
            $this->logApiError($response);
        }
    }

    private function updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss)
    {
        foreach (['tp1', 'tp2', 'tp3'] as $target) {
            $statusField = "{$target}_status";
            if ($$target <= $closePrice && !$signal->$statusField) {
                $signal->$statusField = true;
            }
        }

        if ($stop_loss <= $closePrice && $signal->stop_loss_status === null) {
            $signal->stop_loss_status = $closePrice;
        }

        if ($signal->tp1_status == true && $signal->tp2_status  == true && $signal->tp3_status  == true) {
            $signal->close_price_status = $closePrice;
            $signal->closed = 'yes';

            $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy')
                ? ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0)
                : ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);

            $runningPips = round($runningPips, 2);
            $signal->pips = $runningPips;
        }

        $signal->save();
    }

    private function logApiResponse($currencyPair, $data)
    {
        \Log::info("API response for {$currencyPair}: " . json_encode($data));
    }

    private function logApiError($response)
    {
        \Log::error('API request failed. Status code: ' . $response->status());
    }

    private function closeSignalIfTime($signal, $closePrice)
    {
        $currentTime = now()->format('H:i');
        if ($currentTime === '15:00') {
            $signal->closed = 'yes';
            $signal->close_price_status = $closePrice;

            $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy')
                ? ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0)
                : ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);

            $runningPips = round($runningPips, 2);
            $signal->pips = $runningPips;

            $signal->save();
        }
    }
}
