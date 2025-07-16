<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupabaseVisitorLog extends Model
{
    use HasFactory;

    protected $connection = "pgsql_dragon_4rch1";
    protected $table = "sso_stranger_counters";
}
