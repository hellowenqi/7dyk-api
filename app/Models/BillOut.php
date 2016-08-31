<?php namespace App\Models;

use App\Models\BaseModel;

class BillOut extends BaseModel {

    protected $table = 'bill_out';
    public function __construct() {
        parent::__construct();
        $this->timestamps = true;
    }

    /**
     * @return string
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
