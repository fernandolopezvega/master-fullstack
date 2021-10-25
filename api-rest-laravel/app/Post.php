<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'title', 'content', 'category_id'
    ];

    //Relacion de muchos a uno / muchos post pueden ser creados por usuario / o muchos post pertenecen a una categoria

    public function user(){
     //extrae los post de un usuario
        return $this->belongsTo('App\user', 'user_id');
    }

    public function category(){
        return $this->belongsTo('App\Category', 'category_id');
    }


}
