<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KabupatenIndonesia extends Model
{
    use HasFactory;
    protected $table = "kabupaten_indonesia";
    protected $connection = "mysql_second";
}
