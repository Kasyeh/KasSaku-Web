<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpianModel extends Model
{
    use HasFactory;
    protected $table = 'tb_impian'; // Nama tabel
    protected $primaryKey = 'id_impian'; // Primary key tabel
    protected $fillable = [
        'id_user',
        'nama_barang',
        'foto_barang',
        'harga_barang',
        'deadline',
        'keterangan'
    ];

}
