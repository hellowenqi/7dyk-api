<?php namespace App\Models;

use App\Models\BaseModel;

class Listen extends BaseModel {

    protected $table = 'listen';

    public function __construct() {
        parent::__construct();
    }

    public function answer() {
        return $this->hasOne('App\Models\Answer', 'id', 'answer_id');
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
