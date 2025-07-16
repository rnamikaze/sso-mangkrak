<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikJabatanStrukDos extends Model
{
    use HasFactory;
    protected $table = "sik_jabatan_struk_dos";
    protected $connection = "mysql_second";
}
