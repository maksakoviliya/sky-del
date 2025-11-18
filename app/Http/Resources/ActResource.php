<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ActResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'totalPrice' => $this->totalPrice,
            'totalQuantity' => $this->totalQuantity,
            'totalWeight' => $this->totalWeight,
            'totalCodPrice' => $this->totalCodPrice,
            'orders_count' => count($this->orders),
            'created_at' => $this->created_at,
            'client' => $this->when($request->user()->isAdmin(), new UserResource($this->client))
        ];
    }
}
