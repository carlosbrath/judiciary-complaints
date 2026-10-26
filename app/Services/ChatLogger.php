<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\Complaint;

/**
 * Records chatbot conversations. Transcripts are stored with CNICs, passport and phone numbers
 * masked; the full answers live only in the encrypted `state` column, which lets the chat resume
 * on the next page and lets admins follow up on an unfinished complaint.
 */
class ChatLogger
{
    public function start(?int $citizenId = null): ChatConversation
    {
        return ChatConversation::query()->create([
            'status' => ChatConversation::BROWSING,
            'locale' => app()->getLocale(),
            'citizen_id' => $citizenId,
            'ip' => request()->ip(),
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Saves the component's state and appends the messages not logged yet.
     *
     * @param  list<array{from: string, text: string, tone: ?string, key?: ?string}>  $messages
     * @return int number of messages now logged
     */
    public function sync(ChatConversation $conversation, string $status, array $state, array $messages, int $loggedCount): int
    {
        $new = array_slice($messages, $loggedCount);

        foreach ($new as $message) {
            $conversation->messages()->create([
                'sender' => $message['from'] === 'user' ? 'user' : 'bot',
                'body' => self::mask($message['text']),
                'tone' => $message['tone'] ?? null,
                'question_key' => $message['key'] ?? null,
                'created_at' => now(),
            ]);
        }

        $conversation->fill([
            'status' => $status,
            'state' => $state,
            'current_question' => $status === ChatConversation::FILING ? ($state['current'] ?? null) : null,
            'message_count' => count($messages),
            'locale' => app()->getLocale(),
        ]);

        if ($new) {
            $conversation->last_activity_at = now();
        }

        if ($status === ChatConversation::COMPLETED && ! $conversation->completed_at && filled($state['trackingNumber'] ?? null)) {
            $complaint = Complaint::query()->where('tracking_number', $state['trackingNumber'])->first(['id', 'citizen_id']);
            $conversation->completed_at = now();
            $conversation->complaint_id = $complaint?->id;
            $conversation->citizen_id = $complaint?->citizen_id ?? $conversation->citizen_id;
        }

        $conversation->save();

        return count($messages);
    }

    /** "Start over": an unfinished filing is closed and won't reopen. */
    public function restart(ChatConversation $conversation): void
    {
        if ($conversation->status !== ChatConversation::COMPLETED) {
            $conversation->update(['status' => $conversation->isUnfinished() ? ChatConversation::RESTARTED : $conversation->status]);
        }
    }

    /** Masks CNICs, passport numbers and phone numbers anywhere in a message. Tracking numbers are kept. */
    public static function mask(string $text): string
    {
        // 12345-1234567-1 → 12345-*******-1
        $text = preg_replace('/\b(\d{5})[-\s]?\d{7}[-\s]?(\d)\b/u', '$1-*******-$2', $text);

        // AB1234567 → AB*****67
        $text = preg_replace_callback('/\b([A-Z]{1,3})(\d{4,8})(\d{2})\b/u',
            fn (array $m) => $m[1].str_repeat('*', strlen($m[2])).$m[3], $text);

        // +447700900123 → +44*******123 (not inside tracking or case numbers like OJC-2026-00001)
        return preg_replace_callback('/(?<![\w\-\/*])\+?\d[\d\s-]{6,}\d(?![\w\-\/*])/u', function (array $m) {
            $digits = preg_replace('/\D/', '', $m[0]);

            if (strlen($digits) < 8) {
                return $m[0];
            }

            return (str_starts_with($m[0], '+') ? '+' : '').substr($digits, 0, 2).str_repeat('*', strlen($digits) - 5).substr($digits, -3);
        }, $text);
    }
}
