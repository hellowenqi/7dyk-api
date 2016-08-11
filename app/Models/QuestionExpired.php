<?php namespace App\Models;

use App\Models\BaseModel;

class QuestionExpired extends BaseModel {

    protected $table = 'question_expired';

    public function __construct() {
        parent::__construct();
    }
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'question_user_id');
    }

    public function teacher() {
        return $this->hasOne('App\Models\User', 'id', 'answer_user_id');
    }
}
