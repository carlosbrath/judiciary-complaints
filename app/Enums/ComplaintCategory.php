<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case Delay = 'delay';
    case Adjournments = 'adjournments';
    case StaffConduct = 'staff_conduct';
    case Corruption = 'corruption';
    case Documents = 'documents';
    case Summons = 'summons';
    case Execution = 'execution';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Delay => __('Delay in case proceedings'),
            self::Adjournments => __('Repeated adjournments'),
            self::StaffConduct => __('Conduct of court staff'),
            self::Corruption => __('Corruption or bribery'),
            self::Documents => __('Certified copies / court records'),
            self::Summons => __('Summons or notice not served'),
            self::Execution => __('Decree or order not executed'),
            self::Other => __('Other problem'),
        };
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
