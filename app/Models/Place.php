<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Place extends Model
{
    //
    use HasFactory;

    protected $table = 'places';

    protected $fillable = [
        'name',
        'image_name',
        'image_path',
        'description',
        'location',
        'city',
        'country',
        'location_url',
    ];
    protected $appends = ['public_url'];
    public function getPublicUrlAttribute()
    {
        return Storage::disk('s3')->url($this->image_path);
    }
}
