<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];
    protected $appends = ['containers_count'];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function containers()
    {
        return $this->hasMany(Container::class);
    }
    public function account_statments()
    {
        return $this->hasMany(Account_statment::class);
    }
    public function user_devices()
    {
        return $this->hasMany(User_device::class);
    }
    public function getContainersCountAttribute()
    {
        return $this->containers()->count();
    }
    // let's write  function called files, will get all the files attached to the user from account_statment and containers tables
    public function files()
    {
        $accountStatmentFiles = collect($this->account_statments()->get())->map(function ($file) {
            return [
                'name'   => $file->file_name,
                'url'  => $file->public_url,
                'tracking_number'  => $file->tracking_number,
                'is_delevired'  => $file->is_delevired,
                'delivery_date'  => $file->delivery_date,
                'extension'   => pathinfo($file->file_path, PATHINFO_EXTENSION),
                'type'        => 'bank',
                'date'        => $file->created_at
            ];
        });

        $containerFiles = collect($this->containers()->get())->map(function ($file) {
            return [
                'name'   => $file->file_name,
                'url'  => $file->public_url,
                'tracking_number'  => $file->tracking_number,
                'is_delevired'  => $file->is_delevired,
                'delivery_date'  => $file->delivery_date,
                'extension'   => pathinfo($file->file_path, PATHINFO_EXTENSION),
                'type'        => 'container',
                'date'        => $file->created_at
            ];
        });

        return $accountStatmentFiles->merge($containerFiles);
    }
    public function notifications()
    {
        return $this->hasMany(Notfication::class);
    }

}
