<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Citizen;
use App\Models\Complaint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Registers a complaint against a citizen's account, gives it a tracking number and stores its
 * attachments. The citizen form and the chatbot both go through here.
 */
class ComplaintSubmissionService
{
    public const DISK = 'local';

    /**
     * @param  array{court_id: int, case_number: string, case_title: string, category: string, description: string}  $data
     * @param  list<UploadedFile>  $files
     */
    public function submit(Citizen $citizen, array $data, array $files = [], string $via = Complaint::VIA_FORM, ?string $ip = null): Complaint
    {
        $complaint = DB::transaction(function () use ($citizen, $data, $via, $ip) {
            $complaint = $citizen->complaints()->create([
                ...$data,
                'status' => ComplaintStatus::Submitted,
                'submitted_via' => $via,
                'ip' => $ip,
            ]);

            $complaint->update(['tracking_number' => self::trackingNumber($complaint)]);

            return $complaint;
        });

        foreach ($files as $file) {
            $complaint->attachments()->create([
                'path' => $file->store('complaints/'.$complaint->id, self::DISK),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'mime' => $file->getMimeType(),
                'size' => (int) $file->getSize(),
            ]);
        }

        return $complaint;
    }

    /** OJC-2026-00042: portal prefix, year filed, and the complaint's id. */
    public static function trackingNumber(Complaint $complaint): string
    {
        return sprintf('OJC-%s-%05d', $complaint->created_at->format('Y'), $complaint->id);
    }

    public static function deleteFiles(Complaint $complaint): void
    {
        Storage::disk(self::DISK)->deleteDirectory('complaints/'.$complaint->id);
    }
}
