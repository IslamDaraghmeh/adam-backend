<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'image_name',
        'is_published',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $appends = ['public_url'];
    public function getPublicUrlAttribute()
    {
        return Storage::disk('s3')->url($this->image_path);
    }
}
