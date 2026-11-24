<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedImage extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'path';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['path', 'mime_type', 'content'];

    protected $hidden = ['content'];
}
