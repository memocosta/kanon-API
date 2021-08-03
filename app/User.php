<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $table = 'users';
    protected $with = [
        'clinics',
        'specialitys',
        'member_at',
        'my_clinic',
        'rate_percentage',
        'supported_lang',
        'documents',
        'services',
        
    ];
    public function doctor() {
        return $this->hasMany('App\Doctor', 'user_id', 'user_id');
    }
    function services() {
        return $this->hasMany('App\Service', 'user_id', 'user_id');
    }
    public function clinics() {
        return $this->hasMany('App\Clinic', 'user_id', 'user_id');
    }
    function specialitys() {
        return $this->hasMany('App\SpecialityDoc', 'doc_id', 'user_id')
                        ->join('speciality', 'speciality_doctor.speciality_id', 'speciality.id');
    }
    public function documents() {
        return $this->hasMany('App\Document', 'user_id', 'user_id');
    }
    function member_at() {
        return $this->hasMany('App\ClinicDoctor', 'doctor_id', 'user_id')
                        ->join('clinics', 'clinic_doctor.clinic_id', 'clinics.clinic_id');
    }

    function my_clinic() {
        return $this->hasMany('App\Clinic', 'user_id', 'user_id');
    }
    function rate_percentage() {
        return $this->hasOne('App\RatePercentage', 'doctor_id', 'user_id')
                        ->select([
                            'doctor_id',
                            'r1',
                            'r2',
                            'r3',
                            'r4',
                            'r5'
        ]);
    }
    function supported_lang() {
        return $this->hasMany('App\LangSub', 'user_id', 'user_id')
                        ->join('langs', 'lang_sub.lang_id', 'langs.lang_id');
    }
    public static function get_data($array) {
        return self::where($array)->first();
    }

    public static function exists_data($array) {
        return self::where($array)->exists();
    }

    public static function not_null_name($phone) {
        return self::where('phone', '=', $phone)
                        ->where('first_name', '=', '')
                        ->where('last_name', '=', '')
                        ->exists();
    }

    public static function update_data($where, $update) {
        return self::where($where)->update($update);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'password',
        'phone',
        'country_code',
        'birh_day',
        'gender',
        'avatar',
        'latitude',
        'longitude',
        'height',
        'height_unit',
        'weight',
        'weight_unit',
        'blood_type',
        'activated',
        'active_code'
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
    protected $casts = [
        'user_id' => 'integer',
        'user_type' => 'integer',
        'activated' => 'integer',
        'request_id' => 'integer',
    ];

}
