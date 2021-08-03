<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestClinic extends Model
{

    protected $table = 'requests_clinics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinic_id',
        'questions_answers',
        'status',
    ];

}
