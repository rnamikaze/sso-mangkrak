<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikExtraBiodata extends Model
{
    use HasFactory;
    protected $table = "sik_extra_biodata";
    protected $connection = "mysql_second";
}
