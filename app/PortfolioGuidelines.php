<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioGuidelines extends Model
{
    protected $table = 'portfolio_guidelines';
    protected $guarded = [];
    protected $with = ['guideline_attributes'];
    static $export_associations = ['guideline_attributes'];

    public function guideline_attributes()
    {
        return $this->hasMany('App\GuidelineAttributes', 'guideline_id');
    }

}
