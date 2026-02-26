<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'partnership_id',
        'created_by',
        'name',
        'emoji',
        'target_amount',
        'currency',
        'target_date',
        'status',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_date'   => 'date',
    ];

    // ── Relationships ──

    public function partnership()
    {
        return $this->belongsTo(Partnership::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contributions()
    {
        return $this->hasMany(SavingsContribution::class, 'goal_id');
    }

    // ── Helpers ──

    public function totalSaved(): float
    {
        return (float) $this->contributions()->sum('amount');
    }

    public function progressPercentage(): int
    {
        if ($this->target_amount <= 0) return 0;
        return (int) min(round(($this->totalSaved() / $this->target_amount) * 100), 100);
    }

    public function remaining(): float
    {
        return max(0, (float) $this->target_amount - $this->totalSaved());
    }
}