<?php namespace App\Models;

use App\Models\BaseModel;

class Picture extends BaseModel {

    protected $connection = "mysql_picture";
    protected $table = 'picture';
}
