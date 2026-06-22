<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'full_name'    => $this->first_name . ' ' . $this->last_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'account_type' => $this->account_type,
            'is_approved'  => $this->is_approved,
            'is_active'    => $this->is_active,
            'avatar'       => $this->avatar,
            'settings'     => $this->settings,
            'wallet_balance' => $this->wallet_balance,
            'roles'        => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permissions'  => $this->whenLoaded('permissions', fn() => $this->permissions->pluck('name')),
            'created_at'   => $this->created_at?->toISOString(),
            // NOTE: password, remember_token are never included
        ];
    }
}
