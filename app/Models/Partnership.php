<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partnership extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_a_id',
        'user_b_id',
        'status',
        'anniversary_date',
        'initiated_by',
        'connected_at',
    ];

    protected $casts = [
        'anniversary_date' => 'date',
        'connected_at'     => 'datetime',
    ];

    // ── Relationships ──

    public function userA()
    {
        return $this->belongsTo(User::class, 'user_a_id');
    }

    public function userB()
    {
        return $this->belongsTo(User::class, 'user_b_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    // ── Helpers ──

    public function getPartner(string $userId): User
    {
        return $this->user_a_id === $userId
            ? $this->userB
            : $this->userA;
    }
}