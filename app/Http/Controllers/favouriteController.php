<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Favourite as Favo;

class FavouriteController extends Controller
{
    //
    public static function add (Request $req)
    {
        $setPar = ["user_id", "to_id", "to_type"];
        $getPar = array_keys($req->all());
        $result = array_diff($setPar, $getPar);
        if (!empty($result)) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'Parameters Error',
                'data' => []
            ];
            return response()->json($out, 200, []);
        }
        // print_r( $req->type );exit();
        if ( $req->type == 'true' ) {
            $ex = Favo::where($req->only(['user_id', 'to_id', 'to_type']))->exists();
            if ( $ex == false ) {
                Favo::create($req->only(['user_id', 'to_id', 'to_type']));
                $out = [
                'status' => true,
                    'type' => 'successful',
                    'msg' => 'added',
                    'data' => []
                ];
            } else {
                $out = [
                    'status' => false,
                    'type' => 'dd',
                    'msg' => 'Already added',
                    'data' => []
                ];
            }
        } else {
            $check = Favo::where($req->only(['user_id', 'to_id', 'to_type']))->first();
            if($check){
                Favo::where('favourite_id',$check->favourite_id)->delete();  
                $out = [
                    'status' => true,
                    'type' => 'successful',
                    'msg' => 'delete',
                    'data' => []
                ];
            }else{
                 $out = [
                    'status' => false,
                    'type' => 'dd',
                    'msg' => 'Already Deleted',
                    'data' => []
                ];
            }
        }
        return response()->json($out, 200, []);
    }
}