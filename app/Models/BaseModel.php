<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {
	
	public function __construct() {
		parent::__construct();
        $this->timestamps = false;
	}
}
