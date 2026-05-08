<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceModel extends Model
{
    use HasFactory;
    protected $table = 'tb_saldo_user'; // Nama tabel
    protected $primaryKey = 'id_saldo'; // Primary key tabel
    protected $fillable = [
        'id_user',
        'saldo',
        'pemasukan',
        'pengeluaran',
        'target_pengeluaran',
    ];

}
