<?php

namespace App\Http\Controllers;

use App\Models\ComplaintAttachment;
use App\Services\ComplaintSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/** Streams a complaint file from the private disk to an admin, or to the citizen who filed it. */
class AttachmentDownloadController extends Controller
{
    public function __invoke(Request $request, ComplaintAttachment $attachment)
    {
        if ($request->routeIs('citizen.*')) {
            abort_unless($attachment->complaint->citizen_id === Auth::guard('citizen')->id(), 404);
        }

        $disk = Storage::disk(ComplaintSubmissionService::DISK);
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            // Scripts in an uploaded file can never run on this site.
            'Content-Security-Policy' => 'sandbox',
        ], $request->boolean('download') ? 'attachment' : 'inline');
    }
}
