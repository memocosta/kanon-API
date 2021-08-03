<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\uploadsController as upload;
use App\Http\Controllers\FirebaseController as firebase;
use \App\Token as token;
use \App\User as user;
use \App\chatRoom as Croom;
use \App\chatMassage as CMsg;

class MessagesController extends Controller {

    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 
    public function add_new($req) {
        //Case => message to Admin 
        if ($req->to_id == 0) {
            $user = user::where('user_type', '=', 0)->first();
            $req->to_id = $user['user_id'];
        }

        $exists = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->exists();
        if ($exists == false) {
            $room_id = Croom::create([
                        'from_id' => $req->from_id,
                        'to_id' => $req->to_id,
                        'multiplying_id' => $req->from_id * $req->to_id
                    ])->id;
        } else {
            Croom::where('from_id', '=', $req->from_id)->where('to_id', '=', $req->to_id)->update(['from_id' => $req->from_id]);
            $room_id = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->first()->room_id;
        }
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
        
        date_default_timezone_set('UTC');

        if ($requestData['to_id'] == 0) {
            $user = user::where('user_type', '=', 0)->first();
            $requestData['to_id']= $user['user_id'];
        }

        $requestData['room_id'] = $room_id;
        $requestData['image'] = $filename;
        $requestData['user_id'] = $req->from_id;
        $requestData['created_at'] = date("Y-m-d H:i:s");
        $requestData['updated_at'] = date("Y-m-d H:i:s");
        

        $lastMsg = CMsg::create($requestData);
        CMsg::where('massage_id', '=', $lastMsg['id'])->update(['created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
        
        $lastMsg['massage_id'] = $lastMsg['id'];
        unset($lastMsg['id']);
        $lastMsg['media_url'] =  $this->public_url.$lastMsg['image'];

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'sent Massages',
            'data' => $lastMsg
        ];

        $lastMsg['notificationtype'] = 1;
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
        $lastMsg['user_type'] = $username->user_type;
        
        
        
        if($token = token::get_token(['user_id' => $req->to_id])){
            if (token::is_ios($token)){
                unset($lastMsg['click_action']);
                $lastMsg['content_available'] = true;
            }
            
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Doctor'
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

        $msgs_id = json_decode($req->msg_id, true);
        $users_to = json_decode($req->to, true);
        foreach ($msgs_id as $msg_id => $msg_type) {
            foreach ($users_to as $user_value => $user_type) {
                
                //message type : 1 => document, 2 => chat_message, 3 => chat_message_clinic
                // user_type 1=to my document , 2= to another user , others= chat clinics
                if($msg_type == 1) {
                    $getMsg = DB::table('documents')->where('document_id', $msg_id)->first();
                    $getMsg->massage = $getMsg->document;
                } else if($msg_type == 2) {
                    $getMsg = CMsg::where('massage_id', '=', $msg_id)->first();
                } else {
                    $getMsg = DB::table('chat_massages_clinics')->where('massage_id', $msg_id)->first();
                }

                date_default_timezone_set('UTC');

                if (!is_null($getMsg)) {
                    
                    if($user_type == 1) {
                        DB::table('documents')->insert(
                                [
                                    'user_id' => $req->user_id,
                                    'document' => $getMsg->massage,
                                    'type' => $getMsg->type,
                                    'image' => $getMsg->image,
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]
                        );
                    } else if($user_type == 2) {
                        DB::table('chat_massages')->insert(
                                [
                                    'user_id' => $req->user_id,
                                    'from_id' => $req->user_id,
                                    'to_id' => $user_value,
                                    'massage' => $getMsg->massage,
                                    'status' => 1,
                                    'type' => $getMsg->type,
                                    'is_forward' => 1,
                                    'image' => $getMsg->image,
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]
                        );
                    } else {
                        DB::table('chat_massages_clinics')->insert(
                                [
                                    'user_id' => $req->user_id,
                                    'from_id' => $req->user_id,
                                    'to_id' => $user_value,
                                    'massage' => $getMsg->massage,
                                    'status' => 1,
                                    'type' => $getMsg->type,
                                    'is_forward' => 1,
                                    'image' => $getMsg->image,
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]
                        );
                    }

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
        $payLoad['user_type'] = 'Doctor';
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
                'user_type' => 'Doctor'
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
       // Parameters Error
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
        $payLoad['user_type'] = 'Doctor';
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
        $lastMsg['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req_arr['to']])){
            if (token::is_ios($token)){
                unset($payLoad['click_action']);
                $payLoad['content_available'] = true;
            }
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Doctor'
                    ], $payLoad);
        }

        CMsg::where('user_id', '=', $req_arr['to'])->where('to_id', '=', $req_arr['user_id'])->where('massage_id', '<=', $req_arr['msg_id'])->update(['status' => 3]);
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'seen Massage',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    public function deliver_web(Request $req) {
        
        $getPar = array_keys( $req->all() );
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

        $payLoad['to'] = $req->user_id;
        $payLoad['notificationtype'] = 2;
        $payLoad['user_type'] = 'Doctor';
        $payLoad['click_action'] = 'PasscodeActivty';
        // $username = DB::table('users')->where('user_id', $req->user_id)->first();
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
        
        // if($token = token::get_token(['user_id' => $req->to])){
        //     if (token::is_ios($token)){
        //         unset($payLoad['click_action']);
        //         $payLoad['content_available'] = true;
        //     }
        //     firebase::push($token, [
        //         'title' => 'New Massage',
        //         'body' => 'Massage',
        //         'user_type' => 'Doctor'
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
       // Parameters Error
        
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
        $payLoad['user_type'] = 'Doctor';
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
        $lastMsg['user_name'] = $username->title.' '.$username->first_name.' '.$username->last_name;
        
        if($token = token::get_token(['user_id' => $req->to])){
            if (token::is_ios($token)){
                unset($payLoad['click_action']);
                $payLoad['content_available'] = true;
            }
            firebase::push($token, [
                'title' => 'New Massage',
                'body' => 'Massage',
                'user_type' => 'Doctor'
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
        $room_exists = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->exists();
        if ($room_exists == true) {
            $room_id = Croom::where('multiplying_id', '=', $req->from_id * $req->to_id)->first()->room_id;
            $list = CMsg::whereIn('chat_massages.from_id', [$req->to_id, $req->from_id])->whereIn('chat_massages.to_id', [$req->to_id, $req->from_id])
                    ->select([
                        'chat_massages.massage_id',
                        'chat_massages.status',
                        'chat_massages.room_id',
                        'chat_massages.user_id',
                        'chat_massages.massage',
                        'chat_massages.type',
                        'chat_massages.image',
                        'chat_massages.is_forward',
                        'chat_massages.from_id',
                        'chat_massages.to_id',
                        'chat_massages.created_at',
                    ])
                    ->get();
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'list Massage',
                'data' => $list
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'Oops!',
                'msg' => 'Not Found Room Chat',
                'data' => []
            ];
        }

        return response()->json($out, 200, []);
    }

    public function another(Request $req) {
        $sql1 = "select myuser.* , chat_massages.type , chat_massages.massage , chat_massages.created_at as time
            from chat_massages inner join 
            (SELECT  users . * , MAX( chat_massages.massage_id ) AS massage_id
            FROM users, chat_massages
            WHERE (
            users.user_id = chat_massages.from_id
            OR users.user_id = chat_massages.to_id
            )
            AND users.user_type =2
            AND users.user_id <>" . $req->user_id . "
            AND (
            chat_massages.from_id =" . $req->user_id . "
            OR chat_massages.to_id =" . $req->user_id . ")
            GROUP BY users.user_id
            ) as myuser on chat_massages.massage_id = myuser.massage_id";
        $doctors = \DB::select(\DB::raw($sql1));

        $sql2 = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at as time
            from chat_massages_clinics inner join 
            (SELECT clinics . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
            FROM clinics, chat_massages_clinics
            WHERE (
            clinics.clinic_id = chat_massages_clinics.from_id
            OR clinics.clinic_id = chat_massages_clinics.to_id
            )
            AND clinics.clinic_id <>" . $req->user_id . "
            AND (
            chat_massages_clinics.from_id =" . $req->user_id . "
            OR chat_massages_clinics.to_id =" . $req->user_id . ")
            GROUP BY clinics.clinic_id
            ) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $clinics = \DB::select(\DB::raw($sql2));
        // $rooms = Croom::show_all_room($req);

        foreach ($clinics as $key => $value) {
            unset($clinics[$key]->rate_num);
            unset($clinics[$key]->rate_percentage);
            unset($clinics[$key]->address);
            unset($clinics[$key]->working_hours);
            unset($clinics[$key]->rate_count);
        }

        $users = array_merge($doctors, $clinics);

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
from chat_massages_clinics inner join 
(SELECT clinics . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
FROM clinics, chat_massages_clinics
WHERE (
clinics.clinic_id = chat_massages_clinics.from_id
OR clinics.clinic_id = chat_massages_clinics.to_id
)
AND clinics.clinic_id <>" . $req->user_id . "
AND (
chat_massages_clinics.from_id =" . $req->user_id . "
OR chat_massages_clinics.to_id =" . $req->user_id . ")
GROUP BY clinics.clinic_id
) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $users = \DB::select(\DB::raw($sql));
        // $rooms = Croom::show_all_room($req);
        foreach ($users as $key => $value) {
            unset($users[$key]->rate_num);
            unset($users[$key]->rate_percentage);
            unset($users[$key]->address);
            unset($users[$key]->working_hours);
            unset($users[$key]->rate_count);
        }
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $users
        ];
        return response()->json($out, 200, []);
    }

    public function doctor(Request $req) {
        $sql = "select myuser.* , chat_massages.type , chat_massages.massage , chat_massages.created_at as time
                from chat_massages inner join 
                (SELECT  users . * , MAX( chat_massages.massage_id ) AS massage_id
                FROM users, chat_massages
                WHERE (
                users.user_id = chat_massages.from_id
                OR users.user_id = chat_massages.to_id
                )
                AND users.user_type =2
                AND users.user_id <>" . $req->user_id . "
                AND (
                chat_massages.from_id =" . $req->user_id . "
                OR chat_massages.to_id =" . $req->user_id . ")
                GROUP BY users.user_id
                ) as myuser on chat_massages.massage_id = myuser.massage_id";
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
        $sql = "select myuser.* , chat_massages.type , chat_massages.massage , chat_massages.created_at as time
            from chat_massages inner join 
            (SELECT users . * , MAX( chat_massages.massage_id ) AS massage_id
            FROM users, chat_massages
            WHERE (
            users.user_id = chat_massages.from_id
            OR users.user_id = chat_massages.to_id
            )
            AND users.user_type =1

            AND users.user_id <>" . $req->user_id . "
            AND (
            chat_massages.from_id =" . $req->user_id . "
            OR chat_massages.to_id =" . $req->user_id . ")
            GROUP BY users.user_id
            ) as myuser on chat_massages.massage_id = myuser.massage_id";
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

    public function all(Request $req) {
        $sql1 = "select myuser.* , chat_massages.type , chat_massages.massage , chat_massages.created_at as time
                from chat_massages inner join 
                (SELECT  users . * , MAX( chat_massages.massage_id ) AS massage_id
                FROM users, chat_massages
                WHERE (
                users.user_id = chat_massages.from_id
                OR users.user_id = chat_massages.to_id
                )
                AND users.user_type =2
                AND users.user_id <>" . $req->user_id . "
                AND (
                chat_massages.from_id =" . $req->user_id . "
                OR chat_massages.to_id =" . $req->user_id . ")
                GROUP BY users.user_id
                ) as myuser on chat_massages.massage_id = myuser.massage_id";
        $doctors = \DB::select(\DB::raw($sql1));

        $sql2 = "select myuser.* , chat_massages_clinics.type , chat_massages_clinics.massage , chat_massages_clinics.created_at 
                    as time from chat_massages_clinics inner join 
                    (SELECT clinics . * , MAX( chat_massages_clinics.massage_id ) AS massage_id
                    FROM clinics, chat_massages_clinics
                    WHERE (
                    clinics.clinic_id = chat_massages_clinics.from_id
                    OR clinics.clinic_id = chat_massages_clinics.to_id
                    )
                    AND clinics.clinic_id <>" . $req->user_id . "
                    AND (
                    chat_massages_clinics.from_id =" . $req->user_id . "
                    OR chat_massages_clinics.to_id =" . $req->user_id . ")
                    GROUP BY clinics.clinic_id
                    ) as myuser on chat_massages_clinics.massage_id = myuser.massage_id";
        $clinics = \DB::select(\DB::raw($sql2));
        // $rooms = Croom::show_all_room($req);

        foreach ($clinics as $key => $value) {
            unset($clinics[$key]->rate_num);
            unset($clinics[$key]->rate_percentage);
            unset($clinics[$key]->address);
            unset($clinics[$key]->working_hours);
            unset($clinics[$key]->rate_count);
        }

        $sql3 = "select myuser.* , chat_massages.type , chat_massages.massage , chat_massages.created_at as time
                from chat_massages inner join 
                (SELECT users . * , MAX( chat_massages.massage_id ) AS massage_id
                FROM users, chat_massages
                WHERE (
                users.user_id = chat_massages.from_id
                OR users.user_id = chat_massages.to_id
                )
                AND users.user_type =1
                AND users.user_id <>" . $req->user_id . "
                AND (
                chat_massages.from_id =" . $req->user_id . "
                OR chat_massages.to_id =" . $req->user_id . ")
                GROUP BY users.user_id
                ) as myuser on chat_massages.massage_id = myuser.massage_id";
                        $users = \DB::select(\DB::raw($sql3));

        $another = array_merge($doctors, $clinics);
        
        $all = array_merge($users, $another);

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list anothers',
            'data' => $all
        ];
        return response()->json($out, 200, []);
    }

}


