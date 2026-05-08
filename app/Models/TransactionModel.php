<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionModel extends Model
{
    use HasFactory;
    protected $table = 'tb_transaksi'; // Nama tabel
    protected $primaryKey = 'id_transaksi'; // Primary key tabel
    protected $fillable = [
        'id_user',
        'tipe',        // pemasukan atau pengeluaran
        'nominal',     // jumlah uang
        'kategori',    // opsional
        'keterangan',
        'tanggal',     // tanggal transaksi
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];


    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
