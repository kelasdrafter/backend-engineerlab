<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PremiumTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'thumbnail_url' => $this->product->thumbnail_url,
            ],
            'voucher_code' => $this->voucher_code,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'meta' => $this->meta,
            'snap_id' => $this->snap_id,
            'purchase' => $this->whenLoaded('purchase', function () {
                return [
                    'id' => $this->purchase->id,
                    'status' => $this->purchase->status,
                    'created_at' => $this->purchase->created_at?->toISOString(),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}