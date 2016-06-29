<?php namespace App\Models;

use App\Models\BaseModel;

class Answer extends BaseModel {

    protected $table = 'answer';

    public function __construct() {
        parent::__construct();
    }
    //听过的
    public function answer() {
        return $this->hasOne('App\Models\Answer', 'id', 'answer_id');
    }



}
