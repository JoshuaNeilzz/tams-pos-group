<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function __invoke(string $folder, string $file)
    {
        abort_unless(in_array($folder, ['avatars', 'products'], true) && preg_match('/\A[a-zA-Z0-9_-]+\.(webp|png|jpg|jpeg)\z/', $file), 404);
        $path = $folder.'/'.$file;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), ['X-Content-Type-Options' => 'nosniff']);
        }
        $legacy = public_path('storage/'.$path);
        abort_unless(is_file($legacy), 404);

        return response()->file($legacy, ['X-Content-Type-Options' => 'nosniff']);
    }
}
