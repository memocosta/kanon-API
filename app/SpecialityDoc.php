<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialityDoc extends Model
{

    protected $table = 'speciality_doctor';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'id',
        'speciality_id',
        'doc_id',
        'created_at',
        'updated_at'
    ];

}
