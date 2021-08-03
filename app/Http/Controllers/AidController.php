<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Autoid as AID;

class AidController extends Controller
{
    public static function add ($id, $type)
    {
        $AID_exist = AID::where('row_id', '=', $id)->where('type', '=', $type)->exists();
        if ( $AID_exist == false ) {
            $RSAID = AID::create([ 'row_id' => $id, 'type' => $type ]);
            $out = [
                'id' => $RSAID->id
            ];
        } else {
            $RSAID = AID::where([ 'row_id' => $id, 'type' => $type ])->first();
            $out = [
                'id' => $RSAID->aid
            ];
        }
        return (object) $out;
    }
    
    public static function get ($id, $type)
    {
        $RSAID = AID::where([ 'row_id' => $id, 'type' => $type ])->first();
        $out = [
            'id' => $RSAID->aid,
            'row' => $RSAID->row_id,
        ];
        return (object) $out;
    }
    
    public static function type ($id)
    {
        $RSAID = AID::where([ 'row_id' => $id ])->first();
        return $RSAID['type'];
    }
    
    public static function exists ($id, $type)
    {
        $RSAID = AID::where([ 'row_id' => $id, 'type' => $type ])->exists();
        return $RSAID;
    }
}