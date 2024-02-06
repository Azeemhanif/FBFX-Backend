<?php

namespace App\Console\Commands;

use App\Models\PostSignal;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Storage;
use App\Traits\{NotificationTrait};


class SignalsJob extends Command
{

    use  NotificationTrait;
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
                $closeLivePrice = $currencyData['price'] ?? 0;
                $this->updateSignalStatus($signal, $closeLivePrice, $tp1, $tp2, $tp3, $stop_loss);
                $this->closeSignalIfTime($signal, $closeLivePrice);
            }
        } else {
            $this->logApiError("No Count");
        }
    }

    private function updateSignalStatus($signal, $closeLivePrice, $tp1, $tp2, $tp3, $stop_loss)
    {
        $pipMultiplier = 10000;
        $specialCurrencies = ['XAUUSD', 'BTCUSD', 'ETHUSD', 'BNBUSD', 'ADAUSD', 'XRPUSD', 'XAGUSD'];

        if (in_array($signal->currency, $specialCurrencies)) {
            if ($signal->currency == 'XAUUSD' || $signal->currency == 'XAGUSD') {
                $pipMultiplier = 10;
            } else {
                $pipMultiplier = 1;
            }
            // $pipMultiplier = 1;
        } elseif ($signal->currency === 'USDJPY' || $signal->currency === 'EURJPY' || $signal->currency === 'GBPJPY') {
            $pipMultiplier = 100;
        }

        $isBuy = in_array(strtoupper($signal->action), ['BUY']);
        $isSell = in_array(strtoupper($signal->action), ['SELL']);

        if ($isBuy) {
            $runningLivePips = $closeLivePrice - $signal->open_price;
        } else {
            $runningLivePips = $signal->open_price - $closeLivePrice;
        }
        $runningLivePips = $runningLivePips  * $pipMultiplier;
        $signal->runningLivePips = round($runningLivePips, 2);


        foreach (['tp1', 'tp2', 'tp3'] as $target) {
            $statusField = "{$target}_status";
            $targetValue = $$target;

            if (($isSell && $closeLivePrice <= $targetValue   || $isBuy && $closeLivePrice >= $targetValue) && !$signal->$statusField) {
                if ($isBuy) {
                    $runningPips = $$target - $signal->open_price;
                } else {
                    $runningPips = $signal->open_price - $$target;
                }

                if ($target == 'tp1') {
                    $this->sendNotificationOnTpOneHitting($signal);
                } else {
                    $this->sendNotificationOnTpHitting($signal, $target);
                }

                $runningPips = $runningPips  * $pipMultiplier;
                $signal->pips = round($runningPips, 2);
                $signal->$statusField = true;
            }

            if (($isSell && $closeLivePrice >= $signal->open_price   || $isBuy && $closeLivePrice <= $signal->open_price) && $signal->$statusField == true) {
                $signal->closed = 'yes';
                $this->sendNotificationOnBreakevenCloseSignal($signal);
                // $this->sendNotificationOnAutoCloseSignal($signal, $closeLivePrice);
            }
        }

        if (($isSell && ($signal->tp3_status ||  $closeLivePrice >= $stop_loss)) || ($isBuy && ($signal->tp3_status || $closeLivePrice <= $stop_loss))) {
            $signal->close_price_status = $closeLivePrice;
            if (!$signal->tp3_status) {
                $signal->stop_loss_status = $closeLivePrice;
                if ($isBuy) {
                    $runningPips = $stop_loss - $signal->open_price;
                } else {
                    $runningPips = $signal->open_price - $stop_loss;
                }
                $this->sendNotificationOnSLHitting($signal, $stop_loss);
                $runningPips = $runningPips  * $pipMultiplier;
                $signal->pips = round($runningPips, 2);
            }

            $signal->closed = 'yes';
            $this->sendNotificationOnAutoCloseSignal($signal, $closeLivePrice);
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

    private function closeSignalIfTime($signal, $closeLivePrice)
    {
        $currentTime = now()->format('H:i');
        if ($currentTime === '15:00') {
            // $pipMultiplier = ($signal->currency === 'USDJPY' || $signal->currency === 'EURJPY' || $signal->currency === 'GBPJPY') ? 100 : 10000;
            $signal->closed = 'yes';
            $signal->close_price_status = $closeLivePrice;
            // $signal->stop_loss_status = $closeLivePrice;
            // $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy') ? ($closeLivePrice - $signal->open_price) * $pipMultiplier : ($signal->open_price - $closeLivePrice) * $pipMultiplier;
            // $runningPips = round($runningPips, 2);
            // $signal->pips = $runningPips;
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
