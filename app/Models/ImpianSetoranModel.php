<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpianSetoranModel extends Model
{
    use HasFactory;

    protected $table = 'tb_impian_setoran';
    protected $primaryKey = 'id_setoran_impian';

    protected $fillable = [
        'id_impian',
        'id_user',
        'nominal',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];
}
