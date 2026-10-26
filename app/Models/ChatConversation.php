<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One chatbot conversation, from the citizen's first message until they leave or file a complaint. */
class ChatConversation extends Model
{
    /** FAQs, contact details or tracking only. */
    public const BROWSING = 'browsing';
    /** Answering the filing questions. */
    public const FILING = 'filing';
    /** At the summary, not submitted yet. */
    public const REVIEW = 'review';
    public const COMPLETED = 'completed';
    /** The citizen pressed "Start over". */
    public const RESTARTED = 'restarted';

    public const STATUS_LABELS = [
        self::BROWSING => 'Questions / tracking',
        self::FILING => 'Filing — unfinished',
        self::REVIEW => 'At summary — unfinished',
        self::COMPLETED => 'Complaint filed',
        self::RESTARTED => 'Started over',
    ];

    /** Began filing a complaint but did not submit it. */
    public const UNFINISHED = [self::FILING, self::REVIEW, self::RESTARTED];

    protected $fillable = [
        'status', 'locale', 'current_question', 'message_count', 'state', 'citizen_id', 'complaint_id',
        'ip', 'started_at', 'last_activity_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => 'encrypted:array',
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function scopeUnfinished(Builder $query): void
    {
        $query->whereIn('status', self::UNFINISHED);
    }

    public function isUnfinished(): bool
    {
        return in_array($this->status, self::UNFINISHED, true);
    }
}
