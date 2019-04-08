<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Play;

class User extends Authenticatable /*implements MustVerifyEmail*/
{
    use Notifiable, HasApiTokens;

    private $admin_id = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'provider', 'provider_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function plays()
    {
        return $this->hasMany(Play::class, 'user_id');
    }

    public function isAdmin($user_id)
    {
        return ($user_id == $this->admin_id) ? 'true' : 'false';
    }
}
