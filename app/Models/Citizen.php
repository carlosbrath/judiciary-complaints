<?php

namespace App\Models;

use App\Support\Countries;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * An overseas citizen, identified by CNIC/NICOP or passport number. Authenticates on the `citizen`
 * guard. A complaint filed through the chatbot opens an account without email/password; the
 * citizen claims it later by registering with the same CNIC/passport and WhatsApp number.
 */
class Citizen extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'country', 'phone', 'cnic_passport'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function countryName(): ?string
    {
        return Countries::name($this->country);
    }

    /** Opened by the chatbot and not registered yet. */
    public function isUnclaimed(): bool
    {
        return blank($this->password);
    }

    /** "1234512345671" → "12345-1234567-1"; passport numbers are upper-cased without spaces. */
    public static function normalizeIdentity(?string $value): string
    {
        $value = trim((string) $value);
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) === 13 && preg_match('/^[\d\s-]+$/', $value)) {
            return substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12, 1);
        }

        return strtoupper(preg_replace('/[\s-]+/', '', $value));
    }

    /** "+44 7700 900-123" / "0044…" → "+447700900123". */
    public static function normalizePhone(?string $value): string
    {
        $value = trim((string) $value);
        $digits = preg_replace('/\D/', '', $value);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return $digits === '' ? $value : '+'.$digits;
    }
}
