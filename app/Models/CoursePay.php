<?php namespace App\Models;

use App\Models\BaseModel;

class CoursePay extends BaseModel {
    protected $table = 'course_pay';
    public function __construct() {
        parent::__construct();
    }
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}