<?php namespace App\Models;

use App\Models\BaseModel;

class Chapter extends BaseModel {
    protected $table = 'chapter';
    public function course() {
        return $this->hasOne('App\Models\Course', 'id', 'course_id');
    }
}