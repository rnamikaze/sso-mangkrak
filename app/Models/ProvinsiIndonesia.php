<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinsiIndonesia extends Model
{
    use HasFactory;

    protected $table = "provinsi_indonesia";
    protected $connection = "mysql_second";
}
