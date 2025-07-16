<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaData extends Model
{
    use HasFactory;

    protected $table = "mahasiswa";
    protected $connection = "mysql_sipoma_unusida";
}
