<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model 
{

    protected $table = 'documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinic_id',
        'type',
        'document',
        'image',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'created_at',
        'updated_at',
        // 'user_id',
    ];

//    function sort_by_date($a, $b) {        
//        $al = strtolower($a->created_at);
//        $bl = strtolower($b->created_at);
//        if ($al == $bl) {
//            return 0;
//        }
//        return ($al > $bl) ? +1 : -1;
//    }

}
