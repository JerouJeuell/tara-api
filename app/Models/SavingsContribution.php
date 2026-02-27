<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingsContribution extends Model
{
    use HasUuids;

    protected $fillable = [
        'goal_id',
        'contributed_by',
        'amount',
        'notes',
        'contributed_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'contributed_at' => 'datetime',
    ];

    public function goal()
    {
        return $this->belongsTo(SavingsGoal::class, 'goal_id');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributed_by');
    }
}