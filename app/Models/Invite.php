<?php namespace App\Models;

use App\Models\BaseModel;

class Invite extends BaseModel {

    protected $table = 'invite';

    public function __construct() {
        parent::__construct();
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
