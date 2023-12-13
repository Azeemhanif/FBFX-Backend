<?php

namespace App\Console\Commands;

use App\Models\PostSignal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SignalsJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:signals-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    //before optimizing, and its working,
    public function handle()
    {
        $signals = PostSignal::where('closed', 'no')->get();

        foreach ($signals as $signal) {
            $tp1 = $signal->profit_one;
            $tp2 = $signal->profit_two;
            $tp3 = $signal->profit_three;
            $stop_loss = $signal->stop_loss;
            $currency_pair = $signal->currency_pair;

            // Make API request
            $response = Http::get('https://api.twelvedata.com/time_series', [
                'symbol' => 'AAPL,EUR/USD,ETH/BTC:Huobi,TRP:TSX',
                'interval' => '1min',
                'apikey' => 'acab338c6b924d6ebfa6183fc4a2491e',
            ]);

            // Check if the request was successful
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data) && count($data) > 0) {
                    $EURUSD = null;
                    $closePrice = null;
                    if (isset($data['EUR/USD']) && $currency_pair == 'EUR/USD') {
                        $EURUSD =   $data['EUR/USD'];
                        if (isset($EURUSD)) {
                            $closePrice = $EURUSD['values'][0]['close'];
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }
                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';
                            }
                            $signal->save();
                        }
                    }

                    if (isset($data['AAPL']) && $currency_pair == 'AAPL') {
                        $EURUSD =   $data['AAPL'];
                        if (isset($EURUSD)) {
                            $closePrice = $EURUSD['values'][0]['close'];
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }
                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';
                            }
                            $signal->save();
                        }
                    }

                    if (isset($data['ETH/BTC:Huobi']) && $currency_pair == 'ETH/BTC:Huobi') {
                        $EURUSD =   $data['ETH/BTC:Huobi'];
                        if (isset($EURUSD)) {
                            $closePrice = $EURUSD['values'][0]['close'];
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }
                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';
                            }
                            $signal->save();
                        }
                    }

                    if (isset($data['TRP:TSX']) && $currency_pair == 'TRP:TSX') {
                        $EURUSD =   $data['TRP:TSX'];
                        if (isset($EURUSD)) {
                            $closePrice = $EURUSD['values'][0]['close'];
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }
                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';
                            }
                            $signal->save();
                        }
                    }


                    \Log::info('API response: ' . json_encode($EURUSD));
                }
            } else {
                \Log::error('API request failed. Status code: ' . $response->status());
            }

            $currentTime = date('H:i');
            if ($currentTime === '15:00') {
                $signal->closed = 'yes';
                $signal->close_price_status = $closePrice;

                $signal->save();
            }

            \Log::info('Task started');
        }
    }
}
