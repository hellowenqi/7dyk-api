<?php namespace App\Models;

use App\Models\BaseModel;

class Hot extends BaseModel {

    protected $table = 'hot';
    public function __construct() {
        parent::__construct();
        $this->timestamps = true;
    }
}
