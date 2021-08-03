<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{

    protected $table = 'favourite_doctor';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'to_id',
        'to_type',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
