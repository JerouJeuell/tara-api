<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'partnership_id',
        'created_by',
        'title',
        'event_date',
        'event_time',
        'venue',
        'notes',
        'emoji',
        'is_recurring',
        'recurrence_rule',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'is_recurring' => 'boolean',
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

    public function tags()
    {
        return $this->hasMany(EventTag::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    // ── Scopes ──

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())
                     ->orderBy('event_date', 'asc');
    }
}