<?php

namespace App\Models;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complaint extends Model
{
    public const VIA_FORM = 'form';
    public const VIA_CHATBOT = 'chatbot';

    protected $fillable = [
        'tracking_number', 'citizen_id', 'court_id', 'case_number', 'case_title', 'category',
        'description', 'status', 'submitted_via', 'ip', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => ComplaintCategory::class,
            'status' => ComplaintStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ComplaintReview::class)->orderBy('id');
    }

    /** The newest admin remark (status-only changes without text are skipped). */
    public function latestReview(): HasOne
    {
        return $this->hasOne(ComplaintReview::class)->whereNotNull('body')->latestOfMany();
    }

    public function scopeWithStatus(Builder $query, ComplaintStatus|array $status): void
    {
        $query->whereIn('status', array_map(fn (ComplaintStatus $s) => $s->value, is_array($status) ? $status : [$status]));
    }

    /** A tracking number together with the CNIC/passport it was filed under (chatbot and public lookup). */
    public static function findForTracking(string $trackingNumber, string $identity): ?self
    {
        return static::query()
            ->where('tracking_number', strtoupper(trim($trackingNumber)))
            ->whereHas('citizen', fn (Builder $q) => $q->where('cnic_passport', Citizen::normalizeIdentity($identity)))
            ->with(['court', 'latestReview'])
            ->first();
    }
}
