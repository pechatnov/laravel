<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ApiJson
{
    public static function publicFileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
