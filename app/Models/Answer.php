<?php namespace App\Models;

use App\Models\BaseModel;

class Answer extends BaseModel {

    protected $table = 'answer';

    public function __construct() {
        parent::__construct();
    }

    public function question() {
        return $this->hasOne('App\Models\Question', 'id', 'question_id');
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'question_user_id');
    }

    public function teacher() {
        return $this->hasOne('App\Models\User', 'id', 'answer_user_id');
    }
}
