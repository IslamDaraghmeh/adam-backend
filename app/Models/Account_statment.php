<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Account_statment extends Model
{
    use HasFactory;

    protected $table = 'account_statements';
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
    ];
    protected $appends = ['public_url'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getPublicUrlAttribute()
    {
        return Storage::disk('s3')->url($this->file_path);
    }
}
