<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name == null ? "" : $this->first_name,
            'last_name' => $this->last_name == null ? "" : $this->last_name,
            'email' => $this->email == null ? "" : $this->email,
            'mobile' => $this->mobile == null ? "" : $this->mobile,
            'role' => $this->role == null ? "" : $this->role,
            'image' => $this->image == null ? "" : $this->image,
            'experience' => $this->experience == null ? "" : $this->experience,
            'age' => $this->age == null ? "" : $this->age,
            'gender' => $this->gender == null ? "" : $this->gender,
            'google_token' => $this->google_token == null ? "" : $this->google_token,
            'apple_token' => $this->apple_token == null ? "" : $this->apple_token,
            'social_token' => $this->social_token == null ? "" : $this->social_token,
            'fb_token' => $this->fb_token == null ? "" : $this->fb_token,
            'plan' => $this->plan == null ? "" : $this->plan,
            'is_otp_verified' => $this->is_otp_verified == 1 ? "Y" : "N",
            'is_notification' => $this->is_notification == 1 ? "Y" : "N",
            'is_premium' => $this->is_premium == 1 ? "Y" : "N",
            'package_id' => $this->package_id == null ? "" : $this->package_id,

        ];

        return $data;
    }
}
