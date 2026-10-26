<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An admin follow-up on a complaint: a remark, a status change, or both. */
class ComplaintReview extends Model
{
    protected $fillable = ['user_id', 'status_from', 'status_to', 'body'];

    protected function casts(): array
    {
        return [
            'status_from' => ComplaintStatus::class,
            'status_to' => ComplaintStatus::class,
        ];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changesStatus(): bool
    {
        return $this->status_to !== null && $this->status_to !== $this->status_from;
    }
}
