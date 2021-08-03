<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class chatMassage extends Model
{

    protected $table = 'chat_massages';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'room_id',
        'user_id',
        'from_id',
        'to_id',
        'massage',
        'status',
        'type',
        'image',
        'is_forward',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'room_id',
        'updated_at',
    ];
    

    
}