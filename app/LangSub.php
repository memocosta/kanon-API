<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LangSub extends Model
{

    protected $table = 'lang_sub';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinic_id',
        'lang_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'ls_id',
        'user_id',
        'clinic_id',
//        'lang_id',
        'created_at',
        'updated_at',
    ];
    
    
    public static function addToClinic ($clinic_id, $lang_id) 
    {
        $arrCreate = [
            'clinic_id' => $clinic_id,
            'lang_id' => $lang_id
        ];
        $ex = self::where($arrCreate)->exists();
        if ( $ex == false ) {
            self::create($arrCreate);
        }
    }
    
}
