<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RatePercentage extends Model
{

    protected $table = 'rate_percentage';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'r1',
        'r2',
        'r3',
        'r4',
        'r5',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id_rate_percentage',
        'doctor_id',
        'clinic_id',
        'created_at',
        'updated_at',
    ];
    
    
}
