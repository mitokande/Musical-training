<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified' => $this->hasVerifiedEmail(),
            'avatar_url' => $this->avatar,
            'locale' => $this->locale,
            'country' => $this->country,
            'city' => $this->city,
            'role' => $this->role,
            'plan' => [
                'key' => $this->effectivePlanKey(),
                'is_premium' => $this->isEffectivelyPremium(),
                'expires_at' => $this->plan_expires_at?->toIso8601String(),
                'trial' => [
                    'active' => $this->onTrial(),
                    'ends_at' => $this->trial_ends_at?->toIso8601String(),
                ],
            ],
        ];
    }
}
