<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A court of the lower judiciary; complaints are filed against one. */
class Court extends Model
{
    protected $fillable = ['name_en', 'name_ur', 'district_en', 'district_ur', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /** Court name in the current locale. */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->localized('name'));
    }

    protected function district(): Attribute
    {
        return Attribute::get(fn () => $this->localized('district'));
    }

    /** "Civil Court, Mirpur" — what pickers and summaries show. */
    protected function label(): Attribute
    {
        return Attribute::get(fn () => $this->name.(app()->getLocale() === 'ur' ? '، ' : ', ').$this->district);
    }

    private function localized(string $attribute): ?string
    {
        $ur = $this->{$attribute.'_ur'};

        return app()->getLocale() === 'ur' && filled($ur) ? $ur : $this->{$attribute.'_en'};
    }
}
