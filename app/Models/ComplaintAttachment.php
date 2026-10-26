<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A citizen's file, stored on the private `local` disk and only served through the download routes. */
class ComplaintAttachment extends Model
{
    protected $fillable = ['path', 'original_name', 'mime', 'size'];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
