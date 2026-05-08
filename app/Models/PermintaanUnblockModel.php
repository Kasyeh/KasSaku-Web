<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanUnblockModel extends Model
{
    use HasFactory;
    protected $table = 'tb_permintaan_unblock';
    protected $fillable = ['id_user', 'pesan', 'status', 'alasan_admin'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
