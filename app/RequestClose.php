<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestClose extends Model
{

    protected $table = 'request_close';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_request',
        'status',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
