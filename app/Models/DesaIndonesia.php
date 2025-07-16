<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesaIndonesia extends Model
{
    use HasFactory;
    protected $table = "desa_indonesia";
    protected $connection = "mysql_second";
}
