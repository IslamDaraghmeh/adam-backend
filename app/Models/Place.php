<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return url('storage/' . $this->image_path);
    }
}
