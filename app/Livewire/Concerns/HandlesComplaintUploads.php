<?php

namespace App\Livewire\Concerns;

use App\Support\ComplaintUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Attachment uploads shared by the complaint form and the chatbot. */
trait HandlesComplaintUploads
{
    use WithFileUploads;

    /** @var list<TemporaryUploadedFile> */
    public array $attachments = [];

    /** @var list<TemporaryUploadedFile> Files just dropped/picked; merged into $attachments once validated. */
    public array $pendingAttachments = [];

    public function limits(): array
    {
        return ComplaintUploads::limits();
    }

    public function updatedPendingAttachments(): void
    {
        $limits = $this->limits();

        $this->validate([
            'pendingAttachments.*' => $this->fileRules()['attachments.*'],
        ], attributes: ['pendingAttachments.*' => __('attachment')]);

        $room = $limits['max_files'] - count($this->attachments);

        if (count($this->pendingAttachments) > $room) {
            $this->addError('attachments', __('You can attach up to :max files.', ['max' => $limits['max_files']]));
        }

        $this->attachments = [...$this->attachments, ...array_slice($this->pendingAttachments, 0, max($room, 0))];
        $this->pendingAttachments = [];
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
        $this->resetErrorBag('attachments');
    }

    protected function fileRules(): array
    {
        return ComplaintUploads::rules();
    }

    protected function fileAttributes(): array
    {
        return ['attachments.*' => __('attachment')];
    }
}
