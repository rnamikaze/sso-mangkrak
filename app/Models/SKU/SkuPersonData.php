<?php

namespace App\Models\SKU;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuPersonData extends Model
{
    use HasFactory;

    protected $table = 'sku_person_data';

    protected $fillable = [
        'nik',
        'nama',
        'unit',
        'jabatan',
        'gelar_depan',
        'gelar_belakang',
        'kelamin'
    ];
}
