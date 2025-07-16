<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikKinerjaSubTask extends Model
{
    use HasFactory;

    protected $table = "sik_kinerja_sub_task";
    protected $connection = "mysql_second";
}
