<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IbBrokerResource extends JsonResource
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
            'account_no' => $this->account_no == null ? "" : $this->account_no,
            'email' => $this->email == null ? "" : $this->email,
            'status' => $this->status,
        ];

        return $data;
    }
}
