<?php

namespace App\Services;

use App\Models\UploadedImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class DisplayImage
{
    public function store(UploadedFile $file, string $folder, int $size): string
    {
        $image = (new ImageManager(new Driver))->read($file->getRealPath())->cover($size, $size)->toWebp(85);
        $path = $folder.'/'.Str::uuid().'.webp';
        if (config('media.driver') === 'database') {
            UploadedImage::create(['path' => $path, 'mime_type' => 'image/webp', 'content' => base64_encode((string) $image)]);
        } elseif (! Storage::disk('public')->put($path, (string) $image)) {
            throw new \RuntimeException('The image could not be saved. Please try again.');
        }

        return $path;
    }

    public function delete(string $path): void
    {
        if (config('media.driver') === 'database') {
            UploadedImage::whereKey($path)->delete();
        } else {
            Storage::disk('public')->delete($path);
        }
    }
}
