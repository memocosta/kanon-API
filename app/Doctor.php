<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{

    protected $table = 'doctors';
    protected $with = [
        'specialitys',
        'member_at',
        'my_clinic',
        'rate_percentage',
        'supported_lang',
        'documents',
        'services',
        
    ];
    protected $append = [
        'IsOpen'
    ];

    function documents() {
        return $this->hasMany('App\Document', 'user_id', 'user_id');
    }

    function specialitys() {
        return $this->hasMany('App\SpecialityDoc', 'doc_id', 'user_id')
                        ->join('speciality', 'speciality_doctor.speciality_id', 'speciality.id');
    }
    function services() {
        return $this->hasMany('App\Service', 'user_id', 'user_id');
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

    function supported_lang() {
        return $this->hasMany('App\LangSub', 'user_id', 'user_id')
                        ->join('langs', 'lang_sub.lang_id', 'langs.lang_id');
    }

    function member_at() {
        return $this->hasMany('App\ClinicDoctor', 'doctor_id', 'user_id')
                        ->join('clinics', 'clinic_doctor.clinic_id', 'clinics.clinic_id');
    }

    function my_clinic() {
        return $this->hasMany('App\Clinic', 'user_id', 'user_id');
    }

//    function working_hours() {
//        return $this->hasMany('App\WorkingHour', 'user_id', 'user_id')
//                        ->select([
//                            'user_id',
//                            'monday AS Mon',
//                            'tuesday AS Tue',
//                            'wednesday AS Wed',
//                            'thursday AS Thu',
//                            'friday AS Fri',
//                            'saturday AS Sat',
//                            'sunday AS Sun',
//                        ])
//                        ->where('type_id', '=', 1);
//    }

    public static function get_exist($user_id) {
        return self::where('user_id', '=', $user_id)->exists();
    }

    public static function get_first($user_id) {
        return self::where('user_id', '=', $user_id)->first();
    }

    public static function get_data($user_id) {
        return self::Join('users', 'users.user_id', '=', 'doctors.user_id')
                        ->where('doctors.user_id', '=', $user_id)
//            ->limit(1)
                        ->get()
                        ->first();
        //->toArray();
    }

    public static function exists_data($array) {
        return self::where($array)->exists();
    }

    public static function update_data($where, $update) {
        return self::where($where)->update($update);
    }

    public static function join_user() {
        return self::join('users', 'doctors.user_id', '=', 'users.user_id')
//                        ->isOpen($user_id)
//            ->requestId($user_id)
                        ->select([
                            'users.*',
                            'doctors.*',
//                'request_close.status AS is_open',
//                            \DB::raw("(Select Coalesce(request_close.status, 0)) AS is_open"),
//                'request_close.id_request AS request_id',
//                            \DB::raw("(Select Coalesce(request_close.id_request, 0)) AS request_id"),
                        ])
                        ->where('users.user_type', '=', 2)
                        ->get();
    }

    public static function get_rate($where) {
        return self::where($where)->first()->rate_num;
    }

    public function scopeIsOpen($query, $user_id) {
        $query->leftJoin('requests', 'requests.doc_id', '=', 'doctors.user_id')
                ->leftJoin('request_close', function($query) use ($user_id) {
                    $query->on('request_close.id_request', '=', 'requests.request_id');
                    $query->where('requests.user_id', '=', $user_id);
                });
        return $query;
    }

//    public function scopeIsOpen($query,$user_id)
//    {
//        $query->leftJoin('requests','requests.doc_id','=','doctors.user_id')
//            ->leftJoin('request_close', function($query) use ($user_id) {
//                $query->on('request_close.id_request', '=','requests.request_id');
//                $query->where('requests.user_id', '=', $user_id);
//            });
//        return $query;
//    }

    public function scopeRequestId($query, $user_id) {
        $query->leftJoin('requests', 'requests.doc_id', '=', 'doctors.user_id')
                ->leftJoin('request_close', function($query) use ($user_id) {
                    $query->on('request_close.id_request', '=', 'requests.request_id');
                    $query->where('requests.user_id', '=', $user_id);
                });
        return $query;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'address',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'doctor_id',
//        'user_id',
//        'password',
        'created_at',
        'updated_at',
    ];

}
