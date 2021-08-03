<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Request as Req;
use \App\Rate as Rate;

class FlagController extends Controller {

    //
    public function is_open(Request $req) {

        $requests = \DB::table('requests')->where([
                    ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
                ])->orWhere([
                    ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
                ])
                ->exists();

        if ($requests) {
            $requests = \DB::table('requests')->where([
                        ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
                    ])->orWhere([
                        ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
                    ])
                    ->select('request_id')
                    ->get();
            $out = ['status' => 1, 'request_id' => $requests[0]->request_id];
        } else {
            $out = ['status' => 0, 'request_id' => -1];
        }
//        $requests = Req::where([
//                    ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
//                ])->orWhere([
//                    ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
//                ])
//                ->select('request_id')
//                ->get();
//        foreach ((array) $requests as $requests) {
//            echo '<pre>';
//            var_dump($requests);
//            echo '</pre>';            
//        }
//        die("STOP");
//        if ($exists == true) {
//            $request = Req::where([
//                        ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id]
//                    ])->orWhere([
//                        ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id]
//                    ])->get();
//
//            echo '<pre>';
//            var_dump($request->items);
//            echo '</pre>';
//            die();
//            $out = ['status' => 1];
//        } else {
//            $out = ['status' => 0, 'request_id' => -1];
//        }
//        where('user_id', '=', $req->user_id)
//                ->where('doc_id', '=', $req->doc_id)
//                ->orWhere('user_id', '=', $req->user_id)
//                ->exists();
//        if ($exists == true) {
//            $open = Req::where('user_id', '=', $req->user_id)
//                    ->where('doc_id', '=', $req->doc_id)
//                    ->orderBy('request_id', 'desc')
//                    ->first();
//
//            $out = [
//                'status' => $open->status
//            ];
//        } else {
//            $out = [
//                'status' => 0
//            ];
//        }


        return response()->json($out, 200, []);
    }

    //
    public function have_rate(Request $req) {


        $requests = \DB::table('requests')->where([
                    ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id]
                ])->exists();

        if ($requests) {
            $requests = \DB::table('requests')
                    ->where([ ['requests.user_id', '=', $req->user_id], ['requests.doc_id', '=', $req->doc_id]])
                    ->orderBy('requests.request_id', 'desc')
                    ->get();
            $rates = \DB::table('rate')
                    ->where('req_id', '=', $requests[0]->request_id)
                    ->exists();
            if ($rates) {
                $out = ['status' => 1, 'request_id' => $requests[0]->request_id];
            } else {
                $out = ['status' => 0, 'request_id' => $requests[0]->request_id];
            }
        } else {
            $out = ['status' => 0, 'request_id' => -1];
        }

//        $exist = Rate::where('user_id', '=', $req->user_id)
//                ->where('doctor_id', '=', $req->doc_id)
//                ->exists();
//
//        $out = [
//            'status' => $exist
//        ];

        return response()->json($out, 200, []);
    }

    //
    public function is_open_clinics(Request $req) {

        $requests = \DB::table('requests_clinics')->where([
                    ['user_id', '=', $req->user_id], ['clinic_id', '=', $req->clinic_id], ['status', '=', 1]
                ])->orWhere([
                    ['user_id', '=', $req->clinic_id], ['clinic_id', '=', $req->user_id], ['status', '=', 1]
                ])
                ->exists();

        if ($requests) {
            $requests = \DB::table('requests_clinics')->where([
                        ['user_id', '=', $req->user_id], ['clinic_id', '=', $req->clinic_id], ['status', '=', 1]
                    ])->orWhere([
                        ['user_id', '=', $req->clinic_id], ['clinic_id', '=', $req->user_id], ['status', '=', 1]
                    ])
                    ->select('request_id')
                    ->get();
            $out = ['status' => 1, 'request_id' => $requests[0]->request_id];
        } else {
            $out = ['status' => 0, 'request_id' => -1];
        }
//        $requests = Req::where([
//                    ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
//                ])->orWhere([
//                    ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
//                ])
//                ->select('request_id')
//                ->get();
//        foreach ((array) $requests as $requests) {
//            echo '<pre>';
//            var_dump($requests);
//            echo '</pre>';            
//        }
//        die("STOP");
//        if ($exists == true) {
//            $request = Req::where([
//                        ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id]
//                    ])->orWhere([
//                        ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id]
//                    ])->get();
//
//            echo '<pre>';
//            var_dump($request->items);
//            echo '</pre>';
//            die();
//            $out = ['status' => 1];
//        } else {
//            $out = ['status' => 0, 'request_id' => -1];
//        }
//        where('user_id', '=', $req->user_id)
//                ->where('doc_id', '=', $req->doc_id)
//                ->orWhere('user_id', '=', $req->user_id)
//                ->exists();
//        if ($exists == true) {
//            $open = Req::where('user_id', '=', $req->user_id)
//                    ->where('doc_id', '=', $req->doc_id)
//                    ->orderBy('request_id', 'desc')
//                    ->first();
//
//            $out = [
//                'status' => $open->status
//            ];
//        } else {
//            $out = [
//                'status' => 0
//            ];
//        }


        return response()->json($out, 200, []);
    }

    //
    public function have_rate_clinics(Request $req) {


        $requests = \DB::table('requests_clinics')->where([
                    ['user_id', '=', $req->user_id], ['clinic_id', '=', $req->clinic_id]
                ])->exists();

        if ($requests) {
            $requests = \DB::table('requests_clinics')
                    ->where([ ['requests_clinics.user_id', '=', $req->user_id], ['requests_clinics.clinic_id', '=', $req->clinic_id]])
                    ->orderBy('requests_clinics.request_id', 'desc')
                    ->get();
            $rates = \DB::table('rate')
                    ->where('req_id', '=', $requests[0]->request_id)
                    ->exists();
            if ($rates) {
                $out = ['status' => 1, 'request_id' => $requests[0]->request_id];
            } else {
                $out = ['status' => 0, 'request_id' => $requests[0]->request_id];
            }
        } else {
            $out = ['status' => 0, 'request_id' => -1];
        }

//        $exist = Rate::where('user_id', '=', $req->user_id)
//                ->where('doctor_id', '=', $req->doc_id)
//                ->exists();
//
//        $out = [
//            'status' => $exist
//        ];

        return response()->json($out, 200, []);
    }

    //
    public function request_id(Request $req) {


        $requests = \DB::table('requests')->where([
                    ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
                ])->orWhere([
                    ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
                ])
                ->exists();

        if ($requests) {
            $requests = \DB::table('requests')->where([
                        ['user_id', '=', $req->user_id], ['doc_id', '=', $req->doc_id], ['status', '=', 1]
                    ])->orWhere([
                        ['user_id', '=', $req->doc_id], ['doc_id', '=', $req->user_id], ['status', '=', 1]
                    ])
                    ->select('request_id')
                    ->get();
            $out = ['request_id' => $requests[0]->request_id];
        } else {
            $out = ['request_id' => -1];
        }

//        $exists = Req::where('user_id', '=', $req->user_id)
//                ->where('doc_id', '=', $req->doc_id)
//                ->exists();
//        if ($exists == true) {
//            $open = Req::where('user_id', '=', $req->user_id)
//                    ->where('doc_id', '=', $req->doc_id)
//                    ->orderBy('request_id', 'desc')
//                    ->first();
//
//            $out = [
//                'request_id' => $open->request_id
//            ];
//        } else {
//            $out = [
//                'request_id' => 0
//            ];
//        }


        return response()->json($out, 200, []);
    }

}
