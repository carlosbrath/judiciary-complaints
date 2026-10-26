<?php

namespace App\Support;

/** Upload limits and file rules for a complaint, shared by the web form and the chatbot. */
class ComplaintUploads
{
    public const MAX_FILES = 5;

    public const MAX_FILE_KB = 5120;

    public const FILE_MIMES = 'pdf,jpg,jpeg,png,webp';

    public static function limits(): array
    {
        return ['max_files' => self::MAX_FILES, 'max_file_kb' => self::MAX_FILE_KB];
    }

    public static function rules(string $field = 'attachments'): array
    {
        return [
            $field => ['array', 'max:'.self::MAX_FILES],
            $field.'.*' => ['file', 'max:'.self::MAX_FILE_KB, 'mimes:'.self::FILE_MIMES],
        ];
    }
}
