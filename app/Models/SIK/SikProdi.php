<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikProdi extends Model
{
    use HasFactory;
    protected $table = "sik_prodi";
    protected $connection = "mysql_second";
}
