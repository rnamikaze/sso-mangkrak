<?php

namespace App\Models\SKU;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        "nama_unit",
        "kode_unit",
        "active"
    ];
}
