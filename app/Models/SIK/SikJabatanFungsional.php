<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikJabatanFungsional extends Model
{
    use HasFactory;
    protected $table = "sik_jabatan_fungsional";
    protected $connection = "mysql_second";
}
