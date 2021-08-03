<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model 
{

    protected $table = 'clinics';
    protected $with = [
        'specialitys',
        'supported_lang',
        'documents',
        'member_at',
        //'working_hours', 
        'rate_percentage'
    ];
    protected $casts = [
        'rate_percentage' => 'array',
    ];
    protected $append = [
        'HaveReat',
    ];

//    protected $casts = [
//        'rate_percentage' => 'array',
//    ];
//     protected $with = ['aid'];
//    function aid(){
//         return $this->hasMany('App\Autoid','row_id','clinic_id')->where(['type' => 'clinic']);
//    }
    function documents() {
        return $this->hasMany('App\Document', 'clinic_id', 'clinic_id');
    }
    function specialitys() {
        return $this->hasMany('App\SpecialityClinic', 'clinic_id', 'clinic_id')
                        ->join('speciality', 'speciality.id', 'speciality_clinic.speciality_id');
    }

    function supported_lang() {
        return $this->hasMany('App\LangSub', 'clinic_id', 'clinic_id')
                        ->leftJoin('langs', 'lang_sub.lang_id', 'langs.lang_id');
    }

    function member_at() {
        return $this->hasMany('App\ClinicDoctor', 'clinic_id', 'clinic_id')
                        ->join('users', 'users.user_id', 'clinic_doctor.doctor_id')
                        ->join('doctors', 'doctors.user_id', 'users.user_id');
    }

    function working_hours() {
        return $this->hasMany('App\WorkingHour', 'user_id', 'clinic_id')
                        ->select([
                            'user_id',
                            'monday AS Mon',
                            'tuesday AS Tue',
                            'wednesday AS Wed',
                            'thursday AS Thu',
                            'friday AS Fri',
                            'saturday AS Sat',
                            'sunday AS Sun',
                        ])
                        ->where('type_id', '=', 2);
    }

    function rate_percentage() {
        return $this->hasOne('App\RatePercentage', 'clinic_id', 'clinic_id')
                        ->select([
                            'clinic_id',
                            'r1',
                            'r2',
                            'r3',
                            'r4',
                            'r5'
        ]);
    }

    public function getMemberAtAttribute($value) {

        if (!$this->relations['rate_percentage']) {
            $this->relations['rate_percentage'] = array ();
            return array ();
        }
        return $value;
    }

    public function getRatePercentageAttribute($value) {

        if (!$this->relations['rate_percentage']) {
            $this->relations['rate_percentage'] = array ();
            return array ();
        }
        return $value;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'address',
        'rate_num',
        'rate_percentage',
        'address',
        'street_name',
        'house_number',
        'zip_code',
        'city',
        'providence',
        'county',
        'phone',
        'fax',
        'open_type',
        'avatar',
        'latitude',
        'longitude',
        'working_hours'
//        'supported_lang',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'clinic_id',
//        'user_id',
        'created_at',
        'updated_at',
    ];

    public static function get_all() {
        return self::haveReat()
                        ->select([
                            'clinics.*',
                            'rate.req_id',
                            \DB::raw("(Select Coalesce(rate.req_id, 0)) AS have_rate"),
                        ])
                        ->get();
    }

    public static function add_data($array) {
        return self::create($array);
    }

    public static function get_data($array) {
        return self::where($array)->first();
    }

    public static function edit_data($where, $update) {
        return self::where($where)->update($update);
    }

//    public function scopeHaveReat($query)
//    {
//        $query->leftJoin('rate', function ($query) {
//            $query->on('rate.user_id','=','clinics.clinic_id');
////            $query->orderBy('rate.rate_id', 'desc');
////            $query->first();
//        });
////        $query->leftJoin('rate','rate.user_id','=','clinics.clinic_id')->orderBy('rate_id', 'desc')->first();
//        return $query;
//    }


    public function scopeHaveReat($query) {
        $query->leftJoin('rate', 'rate.user_id', '=', 'clinics.clinic_id')->orderBy('rate_id', 'desc')->first();
        return $query;
    }

    public static function get_rate($where) {
        return self::where($where)->first()->rate_num;
    }

}
