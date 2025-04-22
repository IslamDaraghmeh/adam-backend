<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return url('storage/' . $this->image_path);
    }
}
