<?php

namespace App\Models\SPMB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmbPollData extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll',
        'poll_code'
    ];
}
