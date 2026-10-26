<?php

namespace App\Enums;

/** Submitted → Under Review → In Progress → Resolved / Closed. */
enum ComplaintStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('Submitted'),
            self::UnderReview => __('Under Review'),
            self::InProgress => __('In Progress'),
            self::Resolved => __('Resolved'),
            self::Closed => __('Closed'),
        };
    }

    /** Badge colour (x-ui.badge). */
    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'blue',
            self::UnderReview => 'amber',
            self::InProgress => 'purple',
            self::Resolved => 'brand',
            self::Closed => 'slate',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Resolved, self::Closed], true);
    }

    /** Position on the citizen's timeline (Resolved and Closed share the last step). */
    public function step(): int
    {
        return match ($this) {
            self::Submitted => 1,
            self::UnderReview => 2,
            self::InProgress => 3,
            self::Resolved, self::Closed => 4,
        };
    }

    /** "Pending" on the admin dashboard: not yet being worked on. */
    public static function pending(): array
    {
        return [self::Submitted, self::UnderReview];
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
