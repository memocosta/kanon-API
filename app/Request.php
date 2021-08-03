<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{

    protected $table = 'requests';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'doc_id',
        'questions_answers',
        'status',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'request_id',
        'room_id',
        'user_id',
        'doc_id',
        'created_at',
        'updated_at',
        'type',
        'paid',
    ];

    public static function is_open ($array) {
        $id_r = self::where($array)
            ->where([
                'status' => 1
            ])
            ->first();
        if ( $id_r['status'] == 1 ) {
            $out = true;
        } else {
            $out = false;
        }
        return $out;
    }
    
    
    
}
