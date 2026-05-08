<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\SantriModel;
use App\Models\BalanceModel;
use App\Models\NotificationPreference;
use App\Models\NotificationHistory;
use App\Models\TransactionModel;
use App\Models\ImpianModel;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $fillable = ['username', 'email', 'password', 'role', 'active', 'fcm_token', 'google_id', 'avatar'];
    protected $hidden = ['password', 'remember_token'];
    public $timestamps = false;
    public function santri()
    {
        return $this->hasOne(SantriModel::class, 'id_user');
    }
    public function saldo()
    {
        return $this->hasOne(BalanceModel::class, 'id_user', 'id_user');
    }

    public function dreamItems()
    {
        return $this->hasMany(ImpianModel::class, 'id_user', 'id_user');
    }

    public function transactions()
    {
        return $this->hasMany(TransactionModel::class, 'id_user', 'id_user');
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class, 'id_user', 'id_user');
    }

    public function notificationHistories()
    {
        return $this->hasMany(NotificationHistory::class, 'id_user', 'id_user');
    }
}
