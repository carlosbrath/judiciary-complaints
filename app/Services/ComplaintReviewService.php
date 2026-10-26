<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\ComplaintReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Admin follow-ups: changes a complaint's status and/or records a remark the citizen sees. */
class ComplaintReviewService
{
    public function review(Complaint $complaint, User $admin, ?ComplaintStatus $status, ?string $remark): ComplaintReview
    {
        $remark = filled($remark) ? trim($remark) : null;
        $from = $complaint->status;
        $to = $status && $status !== $from ? $status : null;

        if (! $to && ! $remark) {
            throw new InvalidArgumentException('A review needs a new status or a remark.');
        }

        return DB::transaction(function () use ($complaint, $admin, $from, $to, $remark) {
            if ($to) {
                $complaint->update([
                    'status' => $to,
                    'resolved_at' => $to->isFinal() ? ($complaint->resolved_at ?? now()) : null,
                ]);
            }

            return $complaint->reviews()->create([
                'user_id' => $admin->id,
                'status_from' => $to ? $from : null,
                'status_to' => $to,
                'body' => $remark,
            ]);
        });
    }
}
