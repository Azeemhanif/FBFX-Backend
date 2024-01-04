<?php

namespace App\Console\Commands;

use App\Models\PostSignal;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Storage;

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

        $allowedClasses = ['EUR-USD', 'GBP-USD', 'USD-JPY', 'USD-CAD', 'USD-CHF', 'AUD-USD', 'NZD-USD', 'EUR-JPY', 'GBP-JPY', 'XAU-USD', 'XAG-USD', 'BTC-USD', 'ETH-USD', 'BNB-USD', 'ADA-USD', 'XRP-USD', 'US-30', 'SP-500', 'DXY'];

        $result = $this->scrapeData('https://fxpricing.com/help/get_currencty_list_ajax/forex', $allowedClasses);
        $result2 = $this->scrapeData('https://fxpricing.com/help/get_currencty_list_ajax/crypto', $allowedClasses);

        // Merge the two sets of data into a single array
        $mergedResult = array_merge($result, $result2);

        // Check if the request was successful
        if (count($mergedResult) > 0) {
            if (isset($mergedResult[$currency_pair])) {
                $currencyData = $mergedResult[$currency_pair];
                $closePrice = $currencyData['price'] ?? 0;
                $this->updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss);
                $this->logApiResponse($currency_pair, $currencyData);
                $this->closeSignalIfTime($signal, $closePrice);
            }
        } else {
            $this->logApiError("No Count");
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

            $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy') ? ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0) : ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);

            $runningPips = round($runningPips, 2);
            $signal->pips = $runningPips;
        }

        $signal->save();
    }

    private function logApiResponse($currencyPair, $data)
    {
        $log = "API response for {$currencyPair}: " . json_encode($data);
        self::testjobAction($log);

        // \Log::info("API response for {$currencyPair}: " . json_encode($data));
    }

    private static function testjobAction($msg = 'From test job')
    {
        $file_name = "logs/Cron-" . date('Y-m') . ".txt";
        $time = date('Y-m-d H:i:s');
        Storage::append($file_name, "[{$time}] {$msg} \n");
    }

    private function logApiError($response)
    {
        $log = 'API request failed. Status code: ' . $response->status();
        self::testjobAction($log);
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

    private function scrapeData($url, $allowedClasses)
    {
        $formData = [
            'draw' => '1',
            'start' => '0',
            'length' => '7250'
        ];

        $client = new Client();

        $response = $client->post($url, [
            'form_params' => $formData,
        ]);

        $data = json_decode($response->getBody(), true);
        $result = [];

        foreach ($data['aaData'] as $item) {
            $allClass = $item['all_class'];

            // Check if $allClass exists in the allowed classes
            if (!in_array($allClass, $allowedClasses)) {
                // If $allClass is not in the allowed classes, skip to the next iteration
                continue;
            }

            $key = str_replace('-', '/', $allClass);

            // Extracting the relevant values (price, bid, ask) based on the all_class value
            $price = $item['price'];

            // Create an associative array with all_class as the key and the extracted values as the value
            $result[$key] = [
                'price' => $price,
            ];
        }

        return $result;
    }
}
