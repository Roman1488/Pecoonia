<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Encryptable;

class User extends Authenticatable
{
    use Encryptable;

    // protected $encryptCrypt = [

    // ];

    // protected $encryptBase64 = [

    // ];

    protected $encryptAES256 = [
        'email', 'name', 'user_name'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_name', 'email', 'password', 'timezone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function portfolios()
    {
        return $this->hasMany('App\Portfolio');
    }

    public function securities()
    {
        return $this->hasMany('App\UserSecurity');
    }
}
