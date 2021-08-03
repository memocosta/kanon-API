<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model 
{

    protected $primaryKey = 'session_id';
    public $table='sessions';
    public $timestamps = false;
    protected $fillable = ['user_id','other_id', 'type', 'status', 'payment_type', 'track_id'];



    public static function add_data($array) {
        return self::create($array);
    }

    public static function get_data($array) {
        return self::where($array)->first();
    }

    public static function edit_data($where, $update) {
        return self::where($where)->update($update);
    }
    
}
