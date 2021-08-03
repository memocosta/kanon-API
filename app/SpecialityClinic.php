<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialityClinic extends Model
{

    protected $table = 'speciality_clinic';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'speciality_id',
        'clinic_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'id',
        'speciality_id',
        'clinic_id',
        'created_at',
        'updated_at'
    ];

    public static function addTo ($speciality_id, $clinic_id) 
    {
        $arrCreate = [
            'speciality_id' => $speciality_id,
            'clinic_id' => $clinic_id
        ];
        $ex = self::where($arrCreate)->exists();
        if ( $ex == false ) {
            self::create($arrCreate);
        }
    }
}