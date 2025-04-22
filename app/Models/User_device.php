<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_device extends Model
{
    //
    protected $table = 'user_devices';
    protected $fillable = [
        'user_id',
        'device_token',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
