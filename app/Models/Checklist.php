<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'partnership_id',
        'event_id',
        'created_by',
        'title',
    ];

    // ── Relationships ──

    public function partnership()
    {
        return $this->belongsTo(Partnership::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order');
    }

    // ── Helpers ──

    public function completionPercentage(): int
    {
        $total = $this->items()->count();
        if ($total === 0) return 0;

        $completed = $this->items()->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }
}