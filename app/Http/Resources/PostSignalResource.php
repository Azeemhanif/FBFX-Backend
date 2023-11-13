<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostSignalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id == null ? "" : $this->id,
            'currency_pair' => $this->currency_pair == null ? "" : $this->currency_pair,
            'action' => $this->action == null ? "" : $this->action,
            'stop_loss' => $this->stop_loss == null ? "" : $this->stop_loss,
            'profit_one' => $this->profit_one == null ? "" : $this->profit_one,
            'profit_two' => $this->profit_two == null ? "" : $this->profit_two,
            'profit_three' => $this->profit_three == null ? "" : $this->profit_three,
            'RRR' => $this->RRR == null ? "" : $this->RRR,
            'fvrt' => $this->fvrt == 1 ? "Y" : "N",
            'pips' => $this->pips == null ? "" : $this->pips,
            'worst_pips' => $this->worst_pips == 1 ? "Y" : "N",
            'closed' => $this->closed == null ? "" : $this->closed,
            'close_price' => $this->close_price == null ? "" : $this->close_price,
            'open_price' => $this->open_price == null ? "" : $this->open_price,
            'type' => $this->type == null ? "" : $this->type,
            'role' => $this->role == null ? "" : $this->role,
            'timeframe' => $this->timeframe == null ? "" : $this->timeframe,
            'created_at' => $this->created_at->format('Y-m-d h:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d h:i:s'),

        ];

        return $data;
    }
}
