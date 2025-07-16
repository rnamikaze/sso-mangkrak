<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikKinerjaTask extends Model
{
    use HasFactory;

    protected $table = "sik_kinerja_tasks";
    protected $connection = "mysql_second";
}
