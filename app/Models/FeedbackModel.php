<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackModel extends Model
{
    use HasFactory;

    protected $table = 'tb_feedback';
    protected $primaryKey = 'id_feedback';
    protected $fillable = ['id_user', 'subjek', 'pesan', 'rating', 'is_read', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
