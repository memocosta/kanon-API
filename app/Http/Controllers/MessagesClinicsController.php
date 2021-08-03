<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\uploadsController as upload;
use App\Http\Controllers\FirebaseController as firebase;
use \App\Token as token;
use \App\User as user;
use \App\chatRoom as Croom;
use App\chatMassageClinic as CMsg;

class MessagesClinicsController extends Controller {
    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 
    public function add_new($req) {
//        $exists = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->exists();
//        if ($exists == false) {
//            $room_id = Croom::create([
//                        'from_id' => $req->from_id,
//                        'to_id' => $req->to_id,
//                        'multiplying_id' => $req->from_id * $req->to_id
//                    ])->id;
//        } else {
//            Croom::where('from_id', '=', $req->from_id)->where('to_id', '=', $req->to_id)->update(['from_id' => $req->from_id]);
//            $room_id = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->first()->room_id;
//        }
        // upload

        if (method_exists($req, 'hasFile')) {
            if ($req->hasFile('image') == true) {
                $filename = upload::store($req->image)['data'];
            } else {
                $filename = '';
            }
            $requestData = $req->all();
        } else {
            $requestData = (Array) $req;
            $filename = '';
        }
        // end upload
        //$requestData['room_id'] = $room_id;
        $requestData['image'] = $filename;
        $requestData['user_id'] = $req->from_id;
        if($req->user_id)
        $requestData['user_id'] = $req->user_id;
        
        date_default_timezone_set('UTC');

        $requestData['image'] = $filename;
        $requestData['created_at'] = date("Y-m-d H:i:s");
        $requestData['updated_at'] = date("Y-m-d H:i:s");

        $lastMsg = CMsg::create($requestData);
        CMsg::where('massage_id', '=', $lastMsg['id'])->update(['created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
        

        $lastMsg['massage_id'] = $lastMsg['id'];
        $lastMsg['media_url'] =  $this->public_url.$lastMsg['image'];
        
        unset($lastMsg['id']);

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'sent Massages',
            'data' => $lastMsg
        ];


        $lastMsg['notificationtype'] = 1;
        $lastMsg['user_type'] = 'Clinic';
        $lastMsg['click_action'] = 'PasscodeActivty';
        $username = DB::table('users')->where('user_id', $req->from_id)->first();
        if(!$username){
            $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'User Not Found',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $lastMsg['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req->to_id])){
            if (token::is_ios($token)){
                unset($lastMsg['click_action']);
                $lastMsg['content_available'] = true;
            }
            
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Clinic'
                    ], $lastMsg);
        }

        return $out;
    }

    public function send(Request $req) {
        $req['status'] = 1;
        $out = self::add_new($req);
        return response()->json($out, 200, []);
    }

    public function forward(Request $req) {
        // Parameters Error
//        $getPar = array_keys( $req->all() );
//        $setPar = [
//            "user_id",
//            "to",
//            "msg_id"
//        ];
//        if ( $getPar !== $setPar ) {
//            $out = [
//                'status' => false,
//                'type' => 'Oops',
//                'msg' => 'Parameters Error',
//                'data' => []
//            ];
//            return response()->json($out);
//            exit;
//        }
        ///////////////////////////////////////
        $msgs_id = explode(',', $req->msg_id);

        $users_to = explode(',', $req->to);

        foreach ($msgs_id as $value) {
            foreach ($users_to as $user_value) {
                $getMsg = CMsg::where('massage_id', '=', $value)->first();
                if (!is_null($getMsg)) {
                    $makereq = [
                        'from_id' => $req->user_id,
                        'to_id' => $user_value,
                        'massage' => $getMsg->massage,
                        'status' => 1,
                        'type' => $getMsg->type,
                        'is_forward' => 1,
                        'image' => $getMsg->image,
                    ];
                    $makereq = (object) $makereq;
                    self::add_new($makereq);
                    $out = [
                        'status' => true,
                        'type' => 'successful',
                        'msg' => 'successful forward',
                        'data' => []
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'type' => 'Oops',
                        'msg' => 'error massage',
                        'data' => []
                    ];
                }
            }
        }
        return response()->json($out, 200, []);
    }

    public function delete(Request $req) {
        // Parameters Error
        $getPar = array_keys($req->all());
        $setPar = [
            "user_id",
            "msg_id",
        ];
        if ($getPar !== $setPar) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'Parameters Error',
                'data' => []
            ];
            return response()->json($out);
            exit;
        }
        ///////////////////////////////////////
        CMsg::where('user_id', '=', $req->user_id)->where('massage_id', '=', $req->msg_id)->delete();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'delete Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function deliver(Request $req) {
        if($req->msg_id){
            $req_arr = $req->all();
        }else{

            $ar = $req->all();
            $new_ar = array();
            foreach ($ar as $key => $value) {
                $new_ar = json_decode($key);
            }
            $req_arr = (array)$new_ar;

        }
        $getPar = array_keys( $req_arr );
        $setPar = [
           "msg_id",
           "to",
           "user_id",
       ];

       if ( $getPar !== $setPar ) {
           $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'Parameters Error',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad = [];

        $payLoad['to'] = $req_arr['user_id'];
        $payLoad['notificationtype'] = 2;
        $payLoad['user_type'] = 'Clinic';
        $payLoad['click_action'] = 'PasscodeActivty';
        $username = DB::table('users')->where('user_id', $req_arr['user_id'])->first();
        if(!$username){
            $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'User Not Found',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req_arr['to']])){
            if (token::is_ios($token)){
                unset($payLoad['click_action']);
                $payLoad['content_available'] = true;
            }
            
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Clinic'
                    ], $payLoad);
        }


        CMsg::where('user_id', '=', $req_arr['user_id'])->where('massage_id', '=', $req_arr['msg_id'])->update(['status' => 2]);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'deliver Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function seen(Request $req) {
        if($req->msg_id){
            $req_arr = $req->all();
        }else{

            $ar = $req->all();
            $new_ar = array();
            foreach ($ar as $key => $value) {
                $new_ar = json_decode($key);
            }
            $req_arr = (array)$new_ar;

        }
        $getPar = array_keys( $req_arr );
        $setPar = [
           "msg_id",
           "to",
           "user_id",
       ];

       if ( $getPar !== $setPar ) {
           $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'Parameters Error',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad = [];

        $payLoad['to'] = $req_arr['user_id'];
        $payLoad['notificationtype'] = 3;
        $payLoad['user_type'] = 'Clinic';
        $payLoad['click_action'] = 'PasscodeActivty';
        $username = DB::table('users')->where('user_id', $req_arr['user_id'])->first();
        if(!$username){
            $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'User Not Found',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req_arr['to']])){
            if (token::is_ios($token)){
                unset($payLoad['click_action']);
                $payLoad['content_available'] = true;
            }
            
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Clinic'
                    ], $payLoad);
        }

        CMsg::where('user_id', '=', $req_arr['user_id'])->where('massage_id', '=', $req_arr['msg_id'])->update(['status' => 3]);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'seen Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function deliver_web(Request $req) {

       $getPar = array_keys( $req->all());
       $setPar = [
           "user_id",
       ];

       if ( $getPar !== $setPar ) {
           $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'Parameters Error',
               'data' => []
           ];
           return response()->json($out);
           exit;
       }
        $payLoad = [];

        // $payLoad['to'] = $req_arr['user_id'];
        // $payLoad['notificationtype'] = 2;
        // $payLoad['user_type'] = 'Clinic';
        // $payLoad['click_action'] = 'PasscodeActivty';
        // $username = DB::table('users')->where('user_id', $req_arr['user_id'])->first();
        // if(!$username){
        //     $out = [
        //        'status' => false,
        //        'type' => 'Oops',
        //        'msg' => 'User Not Found',
        //        'data' => []
        //    ];
        //    return response()->json($out);
        //    exit;
        // }
        // $payLoad['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        // if($token = token::get_token(['user_id' => $req_arr['to']])){
        //     if (token::is_ios($token)){
        //         unset($payLoad['click_action']);
        //         $payLoad['content_available'] = true;
        //     }
            
        //     firebase::push($token, [
        //         'title' => 'New Massage',
        //         'body' => 'Massage',
        //         'user_type' => 'Clinic'
        //             ], $payLoad);
        // }

        CMsg::where('to_id', '=', $req->user_id)->where('status', '=', 1)->update(['status' => 2]);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'deliver Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function seen_web(Request $req) {
        $getPar = array_keys( $req->all() );
        $setPar = [
           "user_id",
           "to",
        ];

        if ( $getPar !== $setPar ) {
           $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'Parameters Error',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad = [];

        $payLoad['to'] = $req->user_id;
        $payLoad['notificationtype'] = 3;
        $payLoad['user_type'] = 'Clinic';
        $payLoad['click_action'] = 'PasscodeActivty';
        $username = DB::table('users')->where('user_id', $req->user_id)->first();
        if(!$username){
            $out = [
               'status' => false,
               'type' => 'Oops',
               'msg' => 'User Not Found',
               'data' => []
           ];
           return response()->json($out);
           exit;
        }
        $payLoad['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req->to])){
            if (token::is_ios($token)){
                unset($payLoad['click_action']);
                $payLoad['content_available'] = true;
            }
            
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Clinic'
                    ], $payLoad);
        }
        CMsg::where('from_id', '=', $req->to)->where('to_id', '=', $req->user_id)->update(['status' => 3]);

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'seen Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function messages(Request $req) {
        // Parameters Error
//        $getPar = array_keys( $req->all() );
//        $setPar = [
//            "from_id",
//            "to_id",
//        ];
//        if ( $getPar !== $setPar ) {
//            $out = [
//                'status' => false,
//                'type' => 'Oops',
//                'msg' => 'Parameters Error',
//                'data' => []
//            ];
//            return response()->json($out);
//            exit;
//        }
        ///////////////////////////////////////
//        $room_exists = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->exists();
//        if ($room_exists == true) {
//            $room_id = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->first()->room_id;
        $list = \DB::table('chat_massages_clinics')->where([
                ['chat_massages_clinics.user_id', '=', $req->from_id], ['chat_massages_clinics.from_id', '=', $req->to_id]
        ])->orWhere([
                ['chat_massages_clinics.user_id', '=', $req->from_id], ['chat_massages_clinics.to_id', '=', $req->to_id]
        ])->get();

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list Massage',
            'data' => $list
        ];
//        } else {
//            $out = [
//                'status' => false,
//                'type' => 'Oops!',
//                'msg' => 'Not Found Room Chat',
//                'data' => []
//            ];
//        }

        return response()->json($out, 200, []);
    }

    public function another(Request $req) {
        $sql = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at as time
from chat_massages_clinics inner join 
(SELECT  users . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
FROM users, chat_massages_clinics
WHERE (
users.user_id = chat_massages_clinics.from_id
OR users.user_id = chat_massages_clinics.to_id
)
AND (
users.user_type =2
OR users.user_type =3
)
AND users.user_id <>" . $req->user_id . "
AND (
chat_massages_clinics.from_id =" . $req->user_id . "
OR chat_massages_clinics.to_id =" . $req->user_id . ")
GROUP BY users.user_id
) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $users = \DB::select(\DB::raw($sql));
        // $rooms = Croom::show_all_room($req);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $users
        ];
        return response()->json($out, 200, []);
    }

    public function clinic(Request $req) {
        $sql = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at as time
from chat_massages inner join 
(SELECT users . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
FROM users, chat_massages
WHERE (
users.user_id = chat_massages_clinics.from_id
OR users.user_id = chat_massages_clinics.to_id
)
AND users.user_type =3
AND users.user_id <>" . $req->user_id . "
AND (
chat_massages_clinics.from_id =" . $req->user_id . "
OR chat_massages_clinics.to_id =" . $req->user_id . ")
GROUP BY users.user_id
) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $users = \DB::select(\DB::raw($sql));
        // $rooms = Croom::show_all_room($req);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $users
        ];
        return response()->json($out, 200, []);
    }

    public function doctor(Request $req) {
        $sql = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at as time
from chat_massages inner join 
(SELECT  users . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
FROM users, chat_massages
WHERE (
users.user_id = chat_massages_clinics.from_id
OR users.user_id = chat_massages_clinics.to_id
)
AND users.user_type =2
AND users.user_id <>" . $req->user_id . "
AND (
chat_massages_clinics.from_id =" . $req->user_id . "
OR chat_massages_clinics.to_id =" . $req->user_id . ")
GROUP BY users.user_id
) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $users = \DB::select(\DB::raw($sql));
        //$rooms = Croom::show_all_room($req);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $users
        ];
        return response()->json($out, 200, []);
    }

    public function users(Request $req) {
        $sql = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at as time
from chat_massages_clinics inner join 
(SELECT users . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
FROM users, chat_massages
WHERE (
users.user_id = chat_massages_clinics.from_id
OR users.user_id = chat_massages_clinics.to_id
)
AND users.user_type =1

AND users.user_id <>" . $req->user_id . "
AND (
chat_massages_clinics.from_id =" . $req->user_id . "
OR chat_massages_clinics.to_id =" . $req->user_id . ")
GROUP BY users.user_id
) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $users = \DB::select(\DB::raw($sql));
        //$rooms = Croom::show_all_room($req);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $users
        ];
        return response()->json($out, 200, []);
    }

}
