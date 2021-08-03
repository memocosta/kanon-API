<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\uploadsController as upload;
use App\Http\Controllers\FirebaseController as firebase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use \App\Doctor as doctor;
use \App\Clinic as clinic;
use \App\User as user;
use \App\Rate as rate;
use \App\Token as token;
use \App\RatePercentage as RatePercentage;
use \App\WorkingHour as WH;
use Illuminate\Support\Facades\Input;
use Validator;
use \App\Service as Service;

use Session;


require app_path()."/lib/autoload.php";
use Twilio\Rest\Client;

/*
  1. patient
  2. doctor
 */

class UsersController extends Controller {

    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 

    public function register(Request $req) {
        // Parameters check       
        $setPar = ["country_code", "phone"];
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
        // END Parameters check

        // Start check keys
        /*if (!isset($req->public_key) || !isset($req->private_key)) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => "Please, Don't forget public and private keys",
                'data' => []
            ];
            return response()->json($out, 200, []);
        }*/
        // End check keys

        $user_exist = user::exists_data([
                    'country_code' => $req->country_code,
                    'phone' => $req->phone,
        ]);

        $user_null = user::not_null_name($req->phone);

        if ($user_exist == false) {
            $requestData = $req->only(['country_code', 'password', 'phone', 'public_key', 'private_key']);
            $requestData['password'] = md5(Hash::make(time()));
            $requestData['active_code'] = rand(100000, 999999);
            $requestData['phone_hash'] =  hash('sha256',$req->country_code.$req->phone);
            $user = user::create($requestData);

            /* start :: sending sms code */
                $sid = 'AC0a7cffa388f14597d4392f96c6beca3b';
                $token = '17c0e8997ba85aff4ef24c3f7cd0b903';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $req->country_code.$req->phone,
                    array(
                        'from' => '+1 405 835 6153',
                        'body' => 'Your Verification Code is : '.$user['active_code'],
                    )
                );
            /* end :: sending sms code */

            $data = [
                'user_id' => $user['id'],
                'password' => $user['password'],
                /*'public_key' => $user['public_key'],
                'private_key' => $user['private_key'],*/
                'active_code' => $user['active_code'],
                'phone_hash' => $user['phone_hash'],
                'exists' => false
            ];

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'User registered',
                'data' => $data
            ];

            session()->put('user_data_edit', false);
            
        } else {
            $get_exist = \DB::table('users')->where([
                        ['country_code', '=', $req->country_code], ['phone', '=', $req->phone]
                    ])->get();

            $data = [
                'user_id' => (int) $get_exist[0]->user_id,
                'password' => $get_exist[0]->password,
                /*'public_key' => $get_exist[0]->public_key,
                'private_key' => $get_exist[0]->private_key,*/
                'active_code' => $get_exist[0]->active_code,
                'phone_hash' => $get_exist[0]->phone_hash,
                'exists' => true
            ];
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'User registered',
                'data' => $data
            ];

            session()->put('user_data', $out);
            session()->put('user_data_edit', true);

            /* start :: sending sms code */
                $sid = 'AC0a7cffa388f14597d4392f96c6beca3b';
                $token = '17c0e8997ba85aff4ef24c3f7cd0b903';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $req->country_code.$req->phone,
                    array(
                        'from' => '+1 405 835 6153',
                        'body' => 'Your Verification Code is : '.$get_exist[0]->active_code,
                    )
                );
            /* end :: sending sms code */
        }

        return response()->json($out, 200, []);
    }

    public function login(Request $req) {
        // Parameters check       
        $setPar = ["country_code", "phone"];
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
        // END Parameters check

        // Start check keys
        /*if (!isset($req->public_key) || !isset($req->private_key)) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => "Please, Don't forget public and private keys",
                'data' => []
            ];
            return response()->json($out, 200, []);
        }*/
        // End check keys

        $user_exist = user::exists_data([
                    'country_code' => $req->country_code,
                    'phone' => $req->phone,
        ]);

        $user_null = user::not_null_name($req->phone);

        $get_exist = \DB::table('users')->where([
                    ['country_code', '=', $req->country_code], ['phone', '=', $req->phone]
                ])->get();
        $data = [
            'user_id' => (int) $get_exist[0]->user_id,
            'password' => $get_exist[0]->password,
            /*'public_key' => $get_exist[0]->public_key,
            'private_key' => $get_exist[0]->private_key,*/
            'active_code' => $get_exist[0]->active_code,
            'phone_hash' => $get_exist[0]->phone_hash,
            'phone' => $get_exist[0]->country_code.$get_exist[0]->phone,
            'exists' => true
        ];
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'User active code',
            'data' => $data
        ];
        
        return response()->json($out, 200, []);
    }

    public function add(Request $req) {
        // Parameters check       
       // $setPar = ['user_id', 'password', 'title', 'first_name', 'last_name', 'birh_day', 'gender'];
       // $getPar = array_keys($req->all());
       // $result = array_diff($setPar, $getPar);
       // if ( !empty($result) ) {
       //     $out = [
       //         'status' => false,
       //         'type' => 'Oops',
       //         'msg' => 'Parameters Error',
       //         'data' => []
       //     ];
       //     return response()->json($out);
       //     exit;
       // }
        // END Parameters check
        $user_exist_phone = user::get_data($req->only(['phone']));

        if (!is_null($user_exist_phone)) {
            $out = [
                'status' => false,
                'type' => 'error',
                'msg' => 'Phone already exists',
                'data' => []
            ];
        } else {

            // upload
            // end upload

            $requestData = $req->only([
                'title',
                'first_name',
                'last_name',
                'birh_day',
                'gender'
            ]);

            if ($req->hasFile('avatar') == true) {
                $file = upload::store($req->avatar);
                if (is_array($file)) {
                    $requestData['avatar'] = $file['data'];
                }
            }



            $results = user::where(['user_id' => $req->user_id])
                    ->where(['password' => $req->password])
                    ->update($requestData);

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'User registered',
                'data' => user::get_data($req->only(['user_id', 'password']))
            ];
        }
        return response()->json($out, 200, []);
    }

    public function active(Request $req) {
        $ex = user::exists_data($req->only(['user_id', 'active_code']));
        if ($ex == true) {
            user::update_data($req->only(['user_id', 'active_code']), ['activated' => 1]);
            $selectedUser = user::get_data($req->only(['user_id', 'active_code']));
            $selectedUser->user_id = (int) $selectedUser->user_id;
            $selectedUser->user_type = (int) $selectedUser->user_type;
            $out = [
                'status' => true,
                'type' => 'active user',
                'msg' => 'activated',
                'data' => $selectedUser
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'may be wrong user or not right verify code',
                'msg' => 'not activated',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function switch_to_doctor(Request $req) {
        $ex = doctor::exists_data($req->only(['user_id']));
        if ($ex != true) {
            $userex = user::exists_data($req->only(['user_id']));
            if ($userex == true) {
                user::update_data($req->only(['user_id']), ['user_type' => 2]);
                date_default_timezone_set('UTC');
                DB::table('doctors')->insert(
                        [
                            'user_id' => $req->user_id,
                            'is_available' => 1,
                            'email' => '',
                            'address' => '',
                            'open_type' => 1,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]
                );
                DB::table('rate_percentage')->insert(
                        [
                            'doctor_id' => $req->user_id,
                            'clinic_id' => -1,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]
                );
                $out = [
                    'status' => true,
                    'type' => 'successful',
                    'msg' => 'successful convert to doctor',
                    'data' => []
                ];
            } else {
                $out = [
                    'status' => false,
                    'type' => 'Oops',
                    'msg' => 'This user is not found',
                    'data' => []
                ];
            }
        } else {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'This user is already doctor',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function me(Request $req) {
        $getType = user::get_data($req->only(['user_id']));
        // Parameters check       
        $setPar = ['user_id'];
        $getPar = array_keys($req->all());
        $result = array_diff($setPar, $getPar);
        if (!empty($result)) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'Parameters Error',
                'data' => []
            ];
            return response()->json($out);
        }
        // END Parameters check
        if ($getType->user_type == 1||$getType->user_type == 0) {
            $get = user::get_data($req->only(['user_id']));
        } elseif ($getType->user_type == 2) {
            $get = doctor::get_data($req->only(['user_id']));
        }
        $get->user_id =  (int)$get->user_id;
        $get->open_type =  (int)$get->open_type;
        $get->user_type =  (int)$get->user_type;
        $get->media_url =  $this->public_url.$get->avatar;
        $old_doc = $get->documents;
        $new_doc = [];
        for ($i = count($old_doc) - 1; $i >= 0; $i--) {
            if ($old_doc[$i]->privacy == 2) {
                array_push($new_doc, $old_doc[$i]);
            }
        }
        unset($get->documents);
        $get->documents = $new_doc;
        foreach ($get->documents as $key => $value) {
            $value->media_url = (!empty($value->image)) ? $this->public_url.$value->image : '';
        }


        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'User get all data',
            'data' => $get
        ];

        /* firebase::push(token::get_token($req->only(['user_id'])), [
          'title' => 'Game Request',
          'body' => 'Bob wants to play poker'
          ]); */
        return response()->json((array)$out, 200, [] );
    }

    public function edit(Request $req) {

        $setPar = ["user_id"];
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
            exit;
        }
        $getType = user::where('user_id', '=', $req->user_id)->first();
        if (!$getType) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'Parameters Error',
                'data' => []
            ];
            return response()->json($out, 200, []);
            exit;
        }
        $requestData = $req->only([
            'first_name',
            'last_name',
            'title',
            'phone',
            'country_code',
            'gender',
            'birh_day',
            'avatar',
            'street_name',
            'house_number',
            'providence',
            'zip_code',
            'latitude',
            'longitude',
            'height',
            'height_unit',
            'weight',
            'weight_unit',
            'blood_type',
            "procedure",
            "medical_finding",
            "drugs",
            "allergies",
            "diagnosis",
            "anamnese",
            "address_privacy",
            "medical_privacy",
            "smocking",
            "current_job",
            "chronic_diseases",
            "family_diseases",
            "city",
            "state",
            "country"

        ]);

        $requestkeys = array_keys($requestData);

        foreach ($requestkeys as $requestkey) {
            if (empty($requestData[$requestkey])) {
                unset($requestData[$requestkey]);
            } 
        }
        if(isset($requestData['birh_day'])){
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$requestData['birh_day'])) {
                $out = [
                    'status' => false,
                    'type' => 'Error',
                    'msg' => 'Error in Date Format',
                    'data' => []
                ];
                return response()->json($out, 200, []);
                exit();
            }
        }
        if ($req->hasFile('avatar') == true) {
            $file = upload::store($req->avatar);
            if (is_array($file)) {
                $requestData['avatar'] = $file['data'];
            }
        }

        if ($getType->user_type == 1) {
            user::update_data($req->only(['user_id']), $requestData);
        } elseif ($getType->user_type == 2) {
            user::update_data($req->only(['user_id']), $requestData);
            // up date doc
            doctor::update_data($req->only(['user_id']), $req->only([
                        'email',
            ]));
            if ($req->has('specialities')) {
                $specialities = explode(",", $req->specialities);
                if (!empty($specialities)) {
                    //\DB::table('speciality_doctor')->where('doc_id', $req->user_id)->get()
                    //\DB::table('speciality_doctor')->where('doc_id', $req->user_id)->update(['speciality_id' => 1]);
                    //
                \DB::table('speciality_doctor')->where('doc_id', '=', $req->user_id)->delete();
                    // @todo all specialities ids are valide
                    foreach ($specialities as $speciality) {
                        \DB::table('speciality_doctor')->insert(['doc_id' => $req->user_id, 'speciality_id' => $speciality]);
                    }
                }
            }
            if ($req->has('languages')) {
                $languages = explode(",", $req->languages);
                if (!empty($languages)) {
                    \DB::table('lang_sub')->where('user_id', '=', $req->user_id)->delete();
                    // @todo all languages ids are valide
                    foreach ($languages as $lang) {
                        \DB::table('lang_sub')->insert(['user_id' => $req->user_id, 'lang_id' => $lang]);
                    }
                }
            }
        }

        $getType = user::get_data($req->only(['user_id']));
        // Parameters check   

        
        // END Parameters check
        if ($getType->user_type == 1) {
            $get = user::get_data($req->only(['user_id']));
        } elseif ($getType->user_type == 2) {
            $get = doctor::get_data($req->only(['user_id']));
        }

        $get->user_id = (int) $get->user_id;
        $get->user_type = (int) $get->user_type;
        $old_doc = $get->documents;
        $new_doc = [];

        for ($i = count($old_doc) - 1; $i >= 0; $i--) {
            if ($old_doc[$i]->privacy == 2) {
                array_push($new_doc, $old_doc[$i]);
            }
        }
        unset($get->documents);
        $get->documents = $new_doc;

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'updated',
            'data' => $get
        ];
        return response()->json($out, 200, []);
    }

    public function rate_reviews(Request $req) {

        if ($req->has('doctor_id')) {
            $get_rate = doctor::get_rate($req->only('doctor_id'));
            $get_rate_count = rate::rate_count($req->only('doctor_id'));
            $get_RatePercentage = RatePercentage::where('doctor_id', '=', $req->only('doctor_id'))->get();

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'rate reviews',
                'data' => [
                    'rate' => $get_rate,
                    'rate_count' => $get_rate_count,
                    'rate_persenge' => $get_RatePercentage,
                    'comments' => rate::join('users', 'users.user_id', 'rate.user_id')
                            ->where('doctor_id', '=', $req->doctor_id)
                            ->select([
                                'rate.comment',
                                'rate.rate',
                                'users.user_type',
                                'users.first_name',
                                'users.last_name',
                                'users.title',
                                'users.avatar',
                            ])
                            ->get()
                ]
            ];
        } else if ($req->has('clinic_id')) {
            $get_rate = clinic::get_rate($req->only('clinic_id'));
            $get_rate_count = rate::rate_count($req->only('clinic_id'));
            $get_RatePercentage = RatePercentage::where('clinic_id', '=', $req->only('clinic_id'))->get();
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'rate reviews',
                'data' => [
                    'rate' => $get_rate,
                    'rate_count' => $get_rate_count,
                    'rate_persenge' => $get_RatePercentage,
                    'comments' => rate::join('users', 'users.user_id', 'rate.user_id')
                            ->where('clinic_id', '=', $req->clinic_id)
                            ->select([
                                'rate.comment',
                                'rate.rate',
                                'users.user_type',
                                'users.first_name',
                                'users.last_name',
                                'users.title',
                                'users.avatar',
                            ])
                            ->get()
                ]
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'successful',
                'msg' => 'missing doctor_id or clinic_id',
            ];
            return response()->json($out, 200, []);
        }
        return response()->json($out, 200, []);
    }

    public function add_doctor_working_hours(Request $req) {

        $requestData = $req->all();
        $requestData['type_id'] = 1;
        $req['type_id'] = 1;

        $token_exist = WH::where($req->only(['user_id', 'type_id']))->exists();
        if ($token_exist == false) {
            WH::create($requestData);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'add new token',
                'data' => []
            ];
        } else {
            WH::where($req->only(['user_id', 'type_id']))->update($requestData);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'updated token',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function add_clinic_working_hours(Request $req) {

        $requestData = $req->all();
        $requestData['type_id'] = 2;
        $req['type_id'] = 2;

        $token_exist = WH::where($req->only(['user_id', 'type_id']))->exists();
        if ($token_exist == false) {
            WH::create($requestData);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'add new token',
                'data' => []
            ];
        } else {
            WH::where($req->only(['user_id', 'type_id']))->update($requestData);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'updated token',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function test() {
        $out = clinic::get();
        return response()->json($out, 200, []);
    }

    public function create_pass(Request $req) {

        $requestData = $req->all();

        $token_exist = user::where($req->only(['user_id']))->exists();
        
        $rules = array(
            'user_id' => 'required|numeric',
            'passcode' => 'required|numeric|digits:6',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else if ($token_exist == true ) {
            $requestData['passcode'] = encrypt($req->passcode);
            $requestData['password'] = $requestData['passcode'];
            unset($requestData['passcode']);
            $user = user::where($req->only(['user_id']))->update($requestData);
            $data = [
                    'user_id' => $req->user_id,
                    'passcode' => $req->passcode
                ];
            $out = [
                'status' => true,
                'type' => 'create passcode',
                'msg' => 'passcode created',
                'data' => $data
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'create passcode',
                'msg' => 'user not found',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }
    public function check_pass(Request $req) {

        $requestData = $req->all();

        

        $token_exist = user::where($req->only(['user_id']))->exists();
        $user = user::where($req->only(['user_id']))->first();
        
        $rules = array(
            'user_id' => 'required|numeric',
            'passcode' => 'required|numeric|digits:6',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else if ( $token_exist == true && $user->password) {
            if( decrypt($user->password) == $req->passcode){

                $out = [
                    'status' => true,
                    'type' => 'check passcode',
                    'msg' => 'passcode correct',
                    'data' => $user
                ];
            }else{
                $out = [
                    'status' => false,
                    'type' => 'check passcode',
                    'msg' => 'passcode not correct',
                    'data' => []
                ];
            }
        } else {
            $out = [
                'status' => false,
                'type' => 'check passcode',
                'msg' => 'passcode not correct',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }

    public function add_service(Request $req) {
        $requestData = $req->all();
        
        $rules = array(
            'service' => 'required',
            'price' => 'required|numeric',
            'user_id' => 'required|numeric',
        );
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else {
            $req['created_at'] = date('Y-m-d h:i:s');
            $save = Service::create($req->only(['user_id', 'service', 'price', 'description', 'created_at']));
            $out = [
                'status' => true,
                'type' => 'add service',
                'msg' => 'service added',
                'data' => $save
            ];
        }
        return response()->json($out, 200, []);
    }
    public function single_service(Request $req) {
        $input = $req->all();
        $rules = array(
            'id' => 'required|numeric',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else {
            $data = Service::where('id',$req->id)->first();

            $out = [
                'status' => true,
                'type' => 'service details',
                'msg' => 'service showed',
                'data' => $data
            ];
        }

        
        return response()->json($out, 200, []);
    }

    public function update_service(Request $req) {
        $input = $req->all();
        $rules = array(
            'id' => 'required|numeric',
            'service' => 'required',
            'user_id' => 'required|numeric',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else {
            $requestData = $req->all();
            $update = Service::edit_data($req->only(['id']), $requestData);
            

            $out = [
                'status' => true,
                'type' => 'service details',
                'msg' => 'service showed',
                'data' => $requestData
            ];
        }

        
        return response()->json($out, 200, []);
    }
    public function list_service(Request $req) {
        $input = $req->all();
        $rules = array(
            'user_id' => 'required|numeric',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else {

            $requestData = Service::where('user_id', $req['user_id'])->get();

            $out = [
                'status' => true,
                'type' => 'service list',
                'msg' => 'services showed',
                'data' => $requestData
            ];
        }

        
        return response()->json($out, 200, []);
    }

    public function delete_service(Request $req) {
        $input = $req->all();
        $rules = array(
            'id' => 'required|numeric',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'deleted' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else {
            $check = Service::find($req['id']);
            if($check){
                $check->delete();
                $out = [
                    'status' => true,
                    'deleted' => true,
                    'type' => 'service delete',
                    'msg' => 'service deleted',
                    'data' => []
                ];
            }else{
                $out = [
                'status' => false,
                'deleted' => false,
                'type' => 'Error',
                'msg' => 'Already Deleted',
                'data' => []
            ];
            }
        }

        
        return response()->json($out, 200, []);
    }

    public function sms() {

        $pass=rand(100000,999999); 
        session()->put('pass', 55555);
        session()->put('phone', 'twilio2');
        $sid = 'AC0a7cffa388f14597d4392f96c6beca3b';
        $token = '17c0e8997ba85aff4ef24c3f7cd0b903';
        $client = new Client($sid, $token);
        $client->messages->create(
            '+2001128321285',
            array(
                'from' => '+1 405 835 6153',
                'body' => 'Your pass code is :'.$pass,
            )
        );
        return $pass;
    }

    public function fbase() {
        firebase::push('eUZM-zQKM90:APA91bGS8pIHk8qHYB0l5jvy_Q6qSkcHijRNHTCTxaEV4RXuy5mg3X2f4H73rQK6mPEJXu-6rOn2rlORXHPux0tbODU2hnr2Vph8GAIMMuh83x_aE12N46rGrquIaUJ46Q_EnpWjZgqT', [
          'title' => 'Game Request',
          'body' => 'Bob wants to play poker'
          ]);
        return rand(11111,99999);
    }

}
