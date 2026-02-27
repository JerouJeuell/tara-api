<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingsGoal extends Model
{
    use HasUuids;

    protected $fillable = [
        'partnership_id',
        'created_by',
        'title',
        'emoji',
        'target_amount',
        'current_amount',
        'target_date',
        'notes',
        'is_achieved',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'is_achieved'    => 'boolean',
        'target_date'    => 'date',
    ];

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

    public function totalSaved()
    {
        return $this->contributions()->sum('amount');
    }

    public function progressPercentage()
    {
        if ($this->target_amount <= 0) return 0;
        return min(100, round(($this->totalSaved() / $this->target_amount) * 100));
    }

    public function remaining()
    {
        return max(0, $this->target_amount - $this->totalSaved());
    }
}