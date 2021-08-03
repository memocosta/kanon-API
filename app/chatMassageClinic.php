<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class chatMassageClinic extends Model
{

    protected $table = 'chat_massages_clinics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
    protected $hidden = [];

}
