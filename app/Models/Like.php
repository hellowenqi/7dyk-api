<?php namespace App\Models;

use App\Models\BaseModel;

class Like extends BaseModel {

    protected $table = 'like';

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function answer() {
        return $this->hasOne('App\Models\Answer', 'id', 'answer_id');
    }
}
