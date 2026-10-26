<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** A question and answer the chatbot (and the landing page) shows under "How it works / FAQs". */
class ChatbotFaq extends Model
{
    protected $fillable = ['question_en', 'question_ur', 'answer_en', 'answer_ur', 'keywords', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function localized(string $attribute): string
    {
        $ur = $this->{$attribute.'_ur'};

        return (string) (app()->getLocale() === 'ur' && filled($ur) ? $ur : $this->{$attribute.'_en'});
    }

    /**
     * The FAQ a typed question most likely refers to: keywords and question words it shares.
     *
     * @param  Collection<int, self>  $faqs
     */
    public static function bestMatch(Collection $faqs, string $text): ?self
    {
        $words = self::words($text);

        if (! $words) {
            return null;
        }

        $scored = $faqs->map(function (self $faq) use ($words, $text) {
            $keywords = array_filter(array_map(fn ($k) => Str::lower(trim($k)), explode(',', (string) $faq->keywords)));
            $score = collect($keywords)->filter(fn ($k) => Str::contains(Str::lower($text), $k))->count() * 3;
            $score += count(array_intersect($words, self::words($faq->question_en.' '.$faq->question_ur)));

            return ['faq' => $faq, 'score' => $score];
        })->sortByDesc('score');

        $best = $scored->first();

        return $best && $best['score'] >= 2 ? $best['faq'] : null;
    }

    /** Lower-case words of 3+ letters, minus common filler. */
    private static function words(string $text): array
    {
        $stop = ['the', 'and', 'how', 'what', 'can', 'for', 'you', 'your', 'does', 'with', 'are', 'who', 'when', 'where', 'why', 'which', 'this', 'that', 'کیا', 'کیسے', 'میں', 'ہے', 'کی', 'کا', 'کے', 'اور'];

        return array_values(array_diff(
            array_unique(array_filter(preg_split('/[^\pL\pN]+/u', Str::lower($text)), fn ($w) => mb_strlen($w) >= 3)),
            $stop,
        ));
    }
}
