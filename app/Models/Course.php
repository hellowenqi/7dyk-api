<?php namespace App\Models;

use App\Models\BaseModel;

class Course extends BaseModel {
    protected $table = 'course';
    public function chapters()
    {
        return $this->hasMany('App\Models\Chapter');
    }
}