<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    //Relacion de uno a muchos / una categoria puede estar asignada a muchos post
    public function posts(){
        //$this para aceder al modelo category
        return $this->hasMany('App\Post');
    }
    
}
