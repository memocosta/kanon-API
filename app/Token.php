<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    protected $table = 'token_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'type'
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
    
    public static function get_token($where)
    {
        $ex_token = self::where($where)->exists();
        if ( $ex_token == true ) {
            $token = self::where($where)->first();
            $out = $token->token;
        } else {
            $out = false;
        }
        
        return $out;
    }
    
    public static function is_ios($token)
    {
        $type = self::where('token', $token)->pluck('type')->first();
        
        if ($type == 0){
            return true;
        } else {
            return false;
        }        
    }
}
