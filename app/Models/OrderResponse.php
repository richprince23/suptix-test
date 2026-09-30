<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderResponse extends Model
{
    protected $fillable = [
        'order_id',
        'event_form_field_id',
        'value',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(EventFormField::class, 'event_form_field_id');
    }
}
