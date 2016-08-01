<?php namespace App\Models;

use App\Models\BaseModel;

class User extends BaseModel {

    protected $table = 'user';

    public function __construct() {
        parent::__construct();
    }

    public function teacher() {
        return $this->hasOne('App\Models\Teacher', 'user_id', 'id');
    }
}
