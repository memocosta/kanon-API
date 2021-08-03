<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicDoctor extends Model
{

    protected $table = 'clinic_doctor';
    
    protected $with = ['rate_percentage'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clinic_id',
        'doctor_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'cd_id',
        'doctor_id',
        'created_at',
        'updated_at'
    ];
    
    public static function addTo ($clinic_id, $doctor_id) 
    {
        $arrCreate = [
            'clinic_id' => $clinic_id,
            'doctor_id' => $doctor_id
        ];
        $ex = self::where($arrCreate)->exists();
        if ( $ex == false ) {
            self::create($arrCreate);
        }
    }
    
    function rate_percentage()
    {

         return $this->hasOne('App\RatePercentage','clinic_id', 'clinic_id')
             ->select([
                 'clinic_id',
                 'r1',
                 'r2',
                 'r3',
                 'r4',
                 'r5'
             ]);
    }
    
    public function getRatePercentageAttribute($value){
//        
//        print_r($this);
//        exit;
//        die("----");
        if(!$this->relations['rate_percentage']){
            $this->relations['rate_percentage'] = array();
            return array();   
        }
        return $value; 
    }
}
