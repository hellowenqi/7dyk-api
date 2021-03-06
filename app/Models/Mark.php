<?php namespace App\Models;

use App\Models\BaseModel;

class Mark extends BaseModel {
    protected $table = 'mark';
    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    public function chapter(){
        return $this->hasOne('App\Models\Chapter', 'id', 'chapter_id');
    }
}