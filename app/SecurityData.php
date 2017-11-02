<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecurityData extends Model
{
    use ModelTrait;
    
    protected $table = "security_data";
    protected $guarded = [];
    protected $hidden = ['security_id'];
    protected $with = [];
    
    static $fields_to_exclude = ['security_id'];
    static $export_associations = ['security'];

    public function security()
    {
        return $this->belongsTo('App\Security');
    }
}
