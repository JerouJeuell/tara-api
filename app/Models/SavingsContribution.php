<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SavingsContribution extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'goal_id',
        'contributed_by',
        'amount',
        'note',
        'contributed_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'contributed_at' => 'date',
    ];

    // ── Relationships ──

    public function goal()
    {
        return $this->belongsTo(SavingsGoal::class, 'goal_id');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributed_by');
    }
}