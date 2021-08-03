<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model 
{

    protected $primaryKey = 'id';
    public $table='services';
    public $timestamps = false;
    protected $fillable = ['user_id', 'service', 'price', 'description', 'created_at'];



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
