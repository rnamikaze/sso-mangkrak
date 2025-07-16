<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikUnitKerja extends Model
{
    use HasFactory;
    protected $table = 'sik_unit_kerja';
    protected $connection = "mysql_second";
}
