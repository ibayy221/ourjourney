<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'completed_at',
        'order_index',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'date',
        'order_index' => 'integer',
    ];

    // Scope default order by order_index
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('id');
    }
}
