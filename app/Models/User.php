<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Accessor for phone attribute to format phone number with hyphens.
     *
     * @param string $value
     * @return string
     */
    public function getPhoneAttribute($value)
    {

        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) === 10) {
            return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1-$2-$3', $digits);
        } elseif (strlen($digits) === 11) {
            return preg_replace('/^(\d{3})(\d{4})(\d{4})$/', '$1-$2-$3', $digits);
        }

        return $value; 
    }
}
