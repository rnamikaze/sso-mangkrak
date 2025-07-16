<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikJabatanStruktural extends Model
{
    use HasFactory;
    protected $table = 'sik_jabatan_struktural';
    protected $connection = "mysql_second";
}
