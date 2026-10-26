<?php

namespace App\Support;

use App\Models\Setting;

/** Contact details: values saved by the admin win, otherwise the defaults in config/portal.php. */
class Site
{
    /** Editable fields, in the order the settings screen shows them. */
    public const FIELDS = ['helpline', 'whatsapp', 'email', 'address', 'hours'];

    public static function get(string $key): string
    {
        return (string) (Setting::get('contact.'.$key) ?: config('portal.contact.'.$key, ''));
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return collect(self::FIELDS)->mapWithKeys(fn (string $key) => [$key => self::get($key)])->all();
    }

    /** A dialable tel: link for a number shown to people. */
    public static function tel(string $number): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $number);
    }
}
