<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notfication extends Model
{
    //
    protected $table = 'notfications';
    protected $fillable = ['user_id', 'title', 'body', 'is_read', 'data'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
