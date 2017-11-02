<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSecurity extends Model
{
    protected $table = "user_security";
    protected $with = ['user', 'security', 'portfolio'];
    public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function security()
    {
        return $this->belongsTo('App\Security');
    }
    
    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }
}
