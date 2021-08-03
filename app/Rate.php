<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model 
{

    protected $table = 'rate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'doctor_id',
        'clinic_id',
        'req_id',
        'comment',
        'rate',
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

    public static function rate_count($array) {
        return self::where($array)->get()->count();
    }

}
