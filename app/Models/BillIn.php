<?php namespace App\Models;

use App\Models\BaseModel;

class BillIn extends BaseModel {

    protected $table = 'bill_in';
    public function __construct() {
        parent::__construct();
    }
    public function user() {
        return $this->hasOne('App\Models\App', 'id', 'user_id');
    }
}
