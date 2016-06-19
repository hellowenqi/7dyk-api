<?php namespace App\Models;

use App\Models\BaseModel;

class Question extends BaseModel {

    protected $table = 'question';

	public function __construct() {
		parent::__construct();
    }

    public function answer() {
        return $this->hasOne('App\Models\Answer', 'id', 'answer_id');
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'question_user_id');
    }

    public function teacher() {
        return $this->hasOne('App\Models\User', 'id', 'answer_user_id');
    }

}
