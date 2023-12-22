<?php

namespace App\Http\Resources;

use App\Models\FavouriteSignal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostSignalResource extends JsonResource
{


    public function toArray(Request $request): array
    {

        // get pip value
        $openPrice = $this->open_price;
        $closePriceStatus = $this->close_price_status;
        $action = $this->action;
        $currencyPair = $this->currency_pair;

        // Calculate running pips for different actions and currency pairs
        $runningPips = 0;
        // if ($closePriceStatus != null) {
        //     if ($action == 'buy' || $action == 'Buy') {
        //         $runningPips = ($closePriceStatus - $openPrice) * ($currencyPair === 'EUR/USD' ? 10000 : ($currencyPair === 'JPY/USD' || $currencyPair === 'gold' ? 100 : 0));
        //     } elseif ($action == 'sell' || $action == 'Sell') {
        //         $runningPips = ($openPrice - $closePriceStatus) * ($currencyPair === 'EUR/USD' ? 10000 : ($currencyPair === 'JPY/USD' || $currencyPair === 'gold' ? 100 : 0));
        //     } else {
        //         $runningPips = 0;
        //     }
        //     $runningPips = round($runningPips, 2);
        // }
        $is_favourite  = false;
        $favSignal = FavouriteSignal::where(['user_id' => Auth::user()->id, 'post_signal_id' => $this->id])->first();
        if ($favSignal) $is_favourite  = true;

        //get pips using, open price and close price
        $data = [
            'id' => $this->id == null ? "" : $this->id,
            'currency' => $this->currency == null ? "" : $this->currency,
            'currency_pair' => $this->currency_pair == null ? "" : $this->currency_pair,
            'action' => $this->action == null ? "" : $this->action,
            'stop_loss' => $this->stop_loss == null ? "" : $this->stop_loss,
            'profit_one' => $this->profit_one == null ? "" : $this->profit_one,
            'profit_two' => $this->profit_two == null ? "" : $this->profit_two,
            'profit_three' => $this->profit_three == null ? "" : $this->profit_three,
            'RRR' => $this->RRR == null ? "" : $this->RRR,
            'fvrt' => $this->fvrt == 1 ? "Y" : "N",
            // 'worst_pips' => $runningPips,
            'pips' => $this->pips == null ? 0 : $this->pips,
            'closed' => $this->closed == null ? "" : $this->closed,
            'close_price' => $this->close_price == null ? "" : $this->close_price,
            'open_price' => $this->open_price == null ? "" : $this->open_price,
            'type' => $this->type == null ? "" : $this->type,
            'role' => $this->role == null ? "" : $this->role,
            'is_favourite' => $is_favourite,
            'timeframe' => $this->timeframe == null ? "" : $this->timeframe,
            'tp1_status' => $this->tp1_status == null ? "" : $this->tp1_status,
            'tp2_status' => $this->tp2_status == null ? "" : $this->tp2_status,
            'tp3_status' => $this->tp3_status == null ? "" : $this->tp3_status,
            'close_price_status' => $this->close_price_status == null ? "" : $this->close_price_status,
            'stop_loss_status' => $this->stop_loss_status == null ? "" : $this->stop_loss_status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at == null ? "" : $this->updated_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
