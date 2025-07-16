<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOnlineMonitor extends Model
{
    use HasFactory;

    protected $table = "user_online_monitor";
    protected $connection = "mysql_sso";
}
