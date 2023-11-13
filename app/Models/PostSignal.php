<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSignal extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_pair',
        'action',
        'stop_loss',
        'profit_one',
        'profit_two',
        'profit_three',
        'RRR',
        'timeframe',
        'open_price',
        'user_id',
        'close_price',
        'role',
        'type',
        'id', // Add id to the fillable array
    ];
}
