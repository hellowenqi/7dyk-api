<?php namespace App\Models;

use App\Models\BaseModel;

class Admin extends BaseModel {

    protected $table = 'admin';
    public function __construct() {
        parent::__construct();
        $this->timestamps = true;
    }
}
