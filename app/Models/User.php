<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'password_hash',
        'display_name',
        'avatar_url',
        'invite_code',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    // Tell Laravel our password column is password_hash
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ── Relationships ──

    public function partnerships()
    {
        return $this->hasMany(Partnership::class, 'user_a_id')
                    ->orWhere('user_b_id', $this->id);
    }

    public function activePartnership()
    {
        return $this->hasOne(Partnership::class, 'user_a_id')
                    ->where('status', 'active')
                    ->orWhere(function($q) {
                        $q->where('user_b_id', $this->id)
                          ->where('status', 'active');
                    });
    }
}