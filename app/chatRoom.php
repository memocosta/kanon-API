<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class chatRoom extends Model
{

    protected $table = 'chat_rooms';
    
    protected $append = [
        'IsOpen',
        'LastMsg',
        'HaveReat',
        'IsMyDoctor',
    ];

    protected $with = [
        'specialitys',
        'rate_percentage',
        'supported_lang',
        'documents',
        'member_at',
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_id',
        'to_id',
        'multiplying_id',
        'request_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
    // functions
    function documents()
    {
         return $this->hasMany('App\Document','user_id','user_id');
    }
    
    function specialitys()
    {
         return $this->hasMany('App\SpecialityDoc','doc_id','user_id')
             ->join('speciality', 'speciality_doctor.speciality_id', 'speciality.id');
    }
    
    function rate_percentage()
    {
         return $this->hasOne('App\RatePercentage','doctor_id', 'user_id');
    }
    
    function supported_lang()
    {
        return $this->hasMany('App\LangSub','user_id', 'user_id')
            ->join('langs', 'lang_sub.lang_id', 'langs.lang_id');
    }
    
    function member_at()
    {
        return $this->hasMany('App\ClinicDoctor','doctor_id', 'user_id')
            ->join('clinics', 'clinic_doctor.clinic_id', 'clinics.clinic_id');
    }
    
    public static function show_all_room ($req)
    {
         $rooms = self::join('users', 'users.user_id', 'chat_rooms.to_id')
            ->isOpen()
            ->lastMsg()
            ->haveReat()
            ->IsMyDoctor($req->user_id)
            ->where('chat_rooms.from_id', '=', $req->user_id)
//            ->orWhere('chat_rooms.to_id', '=', $req->user_id)
                ->select([
                    'chat_rooms.room_id',
                    'chat_rooms.from_id AS user_from',
                    'chat_rooms.to_id AS user_to',
                    'chat_rooms.request_id AS request_id',
                    'users.user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.phone',
                    'users.country_code',
                    'users.gender',
                    'users.birh_day',
                    'users.avatar',
                    'users.street_name',
                    'users.house_number',
                    'users.providence',
                    'users.zip_code',
                    'requests.status AS is_open',
                    'chat_massages.massage',
                    'chat_massages.type',
                    'chat_massages.type',
                    'chat_massages.is_forward',
                    'chat_massages.status',
                    'chat_massages.created_at AS time',
                    \DB::raw("(Select Coalesce(rate.req_id, 0)) AS have_rate"),
                    \DB::raw("(Select Coalesce(favourite_doctor.status, 0)) AS is_my_doctor"),
                ])
            ->get();
        return $rooms;
    }
    
    public function scopeIsOpen($query)
    {
        $query->leftJoin('requests','requests.request_id','=','chat_rooms.request_id');
        return $query;
    }
    
    public function scopeLastMsg($query)
    {
        $query->join('chat_massages','chat_massages.room_id','=','chat_rooms.room_id')->orderBy('massage_id', 'desc')->first();
        return $query;
    }
    
    public function scopeHaveReat($query)
    {
        $query->leftJoin('rate','rate.user_id','=','users.user_id')->orderBy('rate_id', 'desc')->first();
        return $query;
    }
    
    public function scopeIsMyDoctor($query,$user_id)
    {
        $query->leftJoin('favourite_doctor', function($query) use ($user_id) {
                $query->on('favourite_doctor.to_id', '=','users.user_id');
                $query->where('favourite_doctor.user_id', '=', $user_id);
            });
        return $query;
    }
}
