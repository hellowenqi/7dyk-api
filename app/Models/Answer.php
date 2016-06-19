<?php namespace App\Models;

use App\Models\BaseModel;

class Answer extends BaseModel {

    protected $table = 'answer';

    public function __construct() {
        parent::__construct();
    }
}
