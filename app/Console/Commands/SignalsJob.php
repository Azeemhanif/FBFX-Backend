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
                'symbol' => 'EUR/USD,GBP/USD,USD/JPY,USD/CAD,USD/CHF,AUD/USD',
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
                            // if total profit 1 hit
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            // if total profit 2 hit

                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            // if total profit 3 hit

                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }

                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            // if all total profit  hits, then we close the signal
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';

                                //if signal is buy, then we get pips
                                if ($signal->action == 'buy' || $signal->action == 'Buy') {
                                    $runningPips = ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                                } else {
                                    //if signal is sell, then we get pips
                                    $runningPips = ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                                }
                                $runningPips = round($runningPips, 2);
                                $signal->pips = $runningPips;
                            }

                            $signal->save();
                        }
                        \Log::info('API response: ' . json_encode($EURUSD));
                    }

                    if (isset($data['GBP/USD']) && $currency_pair == 'GBP/USD') {
                        $GBPUSD =   $data['GBP/USD'];
                        if (isset($GBPUSD)) {
                            $closePrice = $GBPUSD['values'][0]['close'];
                            // if total profit 1 hit
                            if ($tp1 <= $closePrice && $signal->tp1_status != true) {
                                $signal->tp1_status = true;
                            }
                            // if total profit 2 hit

                            if ($tp2 <= $closePrice && $signal->tp2_status != true) {
                                $signal->tp2_status = true;
                            }
                            // if total profit 3 hit

                            if ($tp3 <= $closePrice && $signal->tp3_status != true) {
                                $signal->tp3_status = true;
                            }

                            if ($stop_loss <= $closePrice && $signal->stop_loss_status == null) {
                                $signal->stop_loss_status = $closePrice;
                            }
                            // if all total profit  hits, then we close the signal
                            if ($signal->tp1_status == true && $signal->tp2_status == true && $signal->tp3_status == true) {
                                $signal->close_price_status = $closePrice;
                                $signal->closed = 'yes';

                                //if signal is buy, then we get pips
                                if ($signal->action == 'buy' || $signal->action == 'Buy') {
                                    $runningPips = ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                                } else {
                                    //if signal is sell, then we get pips
                                    $runningPips = ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                                }
                                $runningPips = round($runningPips, 2);
                                $signal->pips = $runningPips;
                            }

                            $signal->save();
                        }
                        \Log::info('API response: ' . json_encode($GBPUSD));
                    }

                    // \Log::info('API response: ' . json_encode($GBPUSD));
                }
            } else {
                \Log::error('API request failed. Status code: ' . $response->status());
            }

            $currentTime = date('H:i');
            // if signal is not closed or time is 8pm, then we close the signal with creating pips
            if ($currentTime === '15:00') {
                $signal->closed = 'yes';
                $signal->close_price_status = $closePrice;

                if ($signal->action == 'buy' || $signal->action == 'Buy') {
                    $runningPips = ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                } else {
                    $runningPips = ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);
                }
                $runningPips = round($runningPips, 2);
                $signal->pips = $runningPips;
                $signal->save();
            }

            \Log::info('Task started');
        }
    }
}
