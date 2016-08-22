<?php namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends BaseModel {

    protected $table = 'history';
    public function __construct() {
        parent::__construct();
        $this->timestamps = true;
    }
    use SoftDeletes;
    protected $dates = ['created_at', 'deleted_at'];
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
