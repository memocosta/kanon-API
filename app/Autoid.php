<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Autoid extends Model
{
    
    protected $table = 'list_auto_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'row_id',
        'type',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'row_id',
        'type',
        'created_at',
        'updated_at',
    ];
}
