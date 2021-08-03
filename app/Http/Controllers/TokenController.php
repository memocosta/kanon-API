<?php
namespace App\Http\Controllers;

use App\Http\Controllers\uploadsController as upload;

use Illuminate\Http\Request;

use \App\Token as token;

class TokenController extends Controller
{
    public function add (Request $req)
    {
        if ( !empty( $req->token ) ) {
            $token_exist = token::where('user_id', '=', $req->user_id)->exists();
            if ( $token_exist == false ) {
                $req['type'] = ($req['type'] == 'ios') ? 0 : 1;
                token::create($req->all());
                $out = [
                    'status' => true,
                    'type' => 'successful',
                    'msg' => 'add new token',
                    'data' => []
                ];
            } else {
                $req['type'] = ($req['type'] == 'ios') ? 0 : 1;
    //            print_r($req->all());
                token::where('user_id', '=', $req->user_id)->update($req->all());
                $out = [
                    'status' => true,
                    'type' => 'successful',
                    'msg' => 'updated token',
                    'data' => []
                ];
            }
        } else {
            $out = [
                'status' => false,
                'type' => 'OOPS!',
                'msg' => 'empty token',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }
    
    public function isIos ($token)
    {
        $type = token::where('token', $token)->pluck('type')->first();
        if($type == 0){
            return true;
        } else {
            return false;
        }
    }
}