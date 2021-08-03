<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\RequestClinic as Req;

//use \App\chatMassageClinic;

class RequestsClinicsController extends Controller {

    public function open(Request $req) {
        $ex = \DB::table('requests_clinics')->where([
                    ['user_id', '=', $req->user_id], ['clinic_id', '=', $req->clinic_id], ['status', '=', 1]
                ])
                ->exists();

        if ($ex != true) {
            $request = Req::create([
                        'user_id' => $req->user_id, 'clinic_id' => $req->clinic_id, 'status' => 1,
                    ])->id;
            ###################################################################
            if ($req->has('questions_answers')) {
                date_default_timezone_set('UTC');
                $questionsAnswersArr = json_decode($req->questions_answers, true);
                foreach ($questionsAnswersArr as $question => $answer) {
                    // echo ' q:' . $question . ' a: ' . $answer . PHP_EOL;
                    \DB::table('chat_massages_clinics')->insert(
                            [
                                'user_id' => $req->user_id,
                                'from_id' => $req->clinic_id,
                                'to_id' => $req->user_id,
                                'massage' => $question,
                                'status' => 1,
                                'type' => 'text',
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s")
                            ]
                    );
                    \DB::table('chat_massages_clinics')->insert(
                            [
                                'user_id' => $req->user_id,
                                'from_id' => $req->user_id,
                                'to_id' => $req->clinic_id,
                                'massage' => $answer,
                                'status' => 1,
                                'type' => 'text',
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s")
                            ]
                    );
                }
            }
            ###################################################################
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'open new request',
                'data' => [
                    'request_id' => $request
                ]
            ];
        } else {
            $out = ['status' => false, 'type' => 'error', 'msg' => 'request exists', 'data' => []];
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

}
