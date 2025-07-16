<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    use HasFactory;

    protected $connection = "mysql_extra";

    protected $fillable = [
        "date_id",
        "login_dump"
    ];
}
