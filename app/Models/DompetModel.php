<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DompetModel extends Model
{
    use HasFactory;
    protected $table = 'tb_dompet'; // Nama tabel
    protected $primaryKey = 'id_dompet'; // Primary key tabel
    protected $fillable = [
        'user_id',
        'nama_dompet',
        'is_active',
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke model TransactionModel
    public function transaksi()
    {
        return $this->hasMany(TransactionModel::class, 'id_dompet');
    }
}
