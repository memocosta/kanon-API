<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model 
{

    protected $table = 'voucher';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'voucher',
        'status',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
//     protected $hidden = [
// //        'created_at',
//         'updated_at',
//         'user_id',
//     ];

//    function sort_by_date($a, $b) {        
//        $al = strtolower($a->created_at);
//        $bl = strtolower($b->created_at);
//        if ($al == $bl) {
//            return 0;
//        }
//        return ($al > $bl) ? +1 : -1;
//    }

}
