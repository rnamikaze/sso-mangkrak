<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KecamatanIndonesia extends Model
{
    use HasFactory;

    protected $table = "kecamatan_indonesia";
    protected $connection = "mysql_second";
}
