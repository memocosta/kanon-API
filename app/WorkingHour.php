<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
type 1 = doc
type 2 = cli
*/

class WorkingHour extends Model
{

    protected $table = 'working_hours';
    
    protected $casts = [
        'Mon' => 'array',
        'Tue' => 'array',
        'Wed' => 'array',
        'Thu' => 'array',
        'Fri' => 'array',
        'Sat' => 'array',
        'Sun' => 'array',
];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type_id',
        'open_type',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'wh_id',
        'user_id',
        'type_id',
        'open_type',
        'created_at',
        'updated_at',
    ];
    
 
}
