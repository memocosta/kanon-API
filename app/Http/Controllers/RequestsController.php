<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Request as Req;
use \App\RequestClose;
use \App\chatRoom as Croom;

class RequestsController extends Controller {

    public function open(Request $req) {
        $ex = Req::is_open($req->only(['user_id', 'doc_id']));

        if ($ex != true) {
            $request = Req::create([
                        'user_id' => $req->user_id,
                        'doc_id' => $req->doc_id,
//                'questions_answers' => $req->questions_answers,
                        'status' => 1,
                    ])->id;
//            $res = RequestClose::create([
//                'id_request' => $request,
//                'status' => 1,
//            ]);
            // new room
//            Croom::create([
//                'from_id' => $req->user_id,
//                'to_id' => $req->doc_id,
//                'multiplying_id' => $req->user_id * $req->doc_id,
//                'request_id' => $request
//            ]);

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'open new request',
                'data' => [
                    'request_id' => $request
                ]
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'error',
                'msg' => 'request exists',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function close(Request $req) {
        $allR = Req::where($req->all())->exists();

        if ($allR == true) {
            $allR = Req::where($req->all())->get();

            foreach ($allR as $value) {
                Req::where([
                    'request_id' => $value->request_id,
                ])->update([ 'status' => 0]);
            }

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'close request',
                'data' => []
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'error',
                'msg' => 'not found request id',
                'data' => []
            ];
        }

        return response()->json($out, 200, []);
    }

    public function all() {
        $list = Req::join('request_closes', 'requests.request_id', '=', 'request_closes.id_request')
                ->select('requests.*', 'request_closes.status')
                ->get();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list requests',
            'data' => $list
        ];
        return response()->json($out, 200, []);
    }

    public function openClinic(Request $req) {
        die("STOPiii");
    }

}
