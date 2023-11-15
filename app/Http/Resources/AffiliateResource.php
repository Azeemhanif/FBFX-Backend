<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateResource extends JsonResource
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
            'GPS' => $this->GPS == null ? "" : $this->GPS,
            'trade' => $this->trade == null ? "" : $this->trade,
            'PAMM' => $this->PAMM == null ? "" : $this->PAMM,
            'IB_broker' => $this->IB_broker == null ? "" : $this->IB_broker,
        ];

        return $data;
    }
}
