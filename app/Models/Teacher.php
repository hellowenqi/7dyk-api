<?php namespace App\Models;

use App\Models\BaseModel;
use App\Models\Question;

class Teacher extends BaseModel {

    protected $table = 'teacher';

    public function __construct() {
        parent::__construct();
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
