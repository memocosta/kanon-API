<?php

namespace App\Http\Controllers;

use App\Http\Controllers\uploadsController as upload;
use Illuminate\Http\Request;
use \App\Clinic as clinic;
use \App\Doctor as doctor;
use \App\LangSub as LS;
use \App\ClinicDoctor as ClinicDoctor;
use \App\SpecialityClinic as SpecialityClinic;
use \App\RatePercentage as RP;
use \App\Rate;
use Validator;
use \App\Favourite as Favo;

//use \App\RatePercentage;

class ClinicsController extends Controller {

    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 

    public function add(Request $req) {
        $exist_doc = doctor::get_exist($req->user_id);
        if ($exist_doc == true) {
            $get_data_doc = doctor::get_first($req->user_id);
            $requestData = $req->all();
            // upload
            if ($req->hasFile('avatar') == true) {
                $file = upload::store($req->avatar);
                if (is_array($file)) {
                    $requestData['avatar'] = $file['data'];
                }
            }
            // end upload
//            $requestData['user_id'] = $get_data_doc->doctor_id;
//            $requestData['avatar'] = $file;
            $results = clinic::create($requestData);
            $requestData['clinic_id'] = $results['id'];
            $requestData['user_id'] = $get_data_doc->doctor_id;
            if ($req->has('specialities')) {
                $specialities = explode(",", $req->specialities);
                if (!empty($specialities)) {
                    // @todo all specialities ids are valide
                    foreach ($specialities as $speciality) {
                        \DB::table('speciality_clinic')->insert(['clinic_id' => $results['id'], 'speciality_id' => $speciality]);
                    }
                }
            }
            if ($req->has('languages')) {
                $languages = explode(",", $req->languages);
                if (!empty($languages)) {
                    // @todo all languages ids are valide
                    foreach ($languages as $lang) {
                        \DB::table('lang_sub')->insert(['clinic_id' => $results['id'], 'lang_id' => $lang]);
                    }
                }
            }
            if ($req->has('member_at')) {
                $member_ats = explode(",", $req->member_at);
                if (!empty($member_ats)) {
                    // @todo all languages ids are valide
                    foreach ($member_ats as $member_at) {
                        \DB::table('clinic_doctor')->insert(['clinic_id' => $results['id'], 'doctor_id' => $member_at]);
                    }
                }
            }
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'Dr. was successfully added',
                'data' => $requestData
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'Oops!',
                'msg' => 'exists doctor',
                'data' => []
            ];
        }
//        if (strpos($req->speciality, ',') == true) {
//            $speciality_list = explode(",", $req->speciality);
//            foreach ($speciality_list as $value) {
//                SpecialityClinic::addTo($value, $results->id);
//            }
//        } else {
//            SpecialityClinic::addTo($req->speciality, $results->id);
//        }
//        if (strpos($req->lang_sub, ',') == true) {
//            $lang_list = explode(",", $req->lang_sub);
//            foreach ($lang_list as $value) {
//                LS::addToClinic($results->id, $value);
//            }
//        } else {
//            LS::addToClinic($results->id, $req->lang_sub);
//        }
//        if (strpos($req->member_at, ',') == true) {
//            $lang_list = explode(",", $req->member_at);
//            foreach ($lang_list as $value) {
//                ClinicDoctor::addTo($results->id, $value);
//            }
//        } else {
//            ClinicDoctor::addTo($results->id, $req->member_at);
//        }


        RP::create(['clinic_id' => $results->id]);


        return response()->json($out, 200, []);
    }

    public function get(Request $req) {
         $requestData = $req->all();
        
        $rules = array(
            'clinic_id' => 'required|numeric',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
            return response()->json($out, 200, []);
            exit();
        } else{
            $list = clinic::get_data([
                        'clinic_id' => $req->clinic_id
            ]);
            if(!$list){
                $out = [
                    'status' => false,
                    'type' => 'Oops',
                    'msg' => 'Parameters Error',
                    'data' => []
                ];
                return response()->json($out, 200, []);
                exit;
            }
            $list->is_online = false;
            if ($list->open_type == 3) {
                $dayName = strtoupper(date("D"));
                $timeNow = new \DateTime(date("g:i a"));
                $workingHoursArr = json_decode(str_replace("'", "", $list['working_hours']), true);
                if (!empty($workingHoursArr) && isset($workingHoursArr[$dayName])) {
                    foreach ($workingHoursArr[$dayName] as $arr) {
                        if (
                                $timeNow->diff(new \DateTime($arr['from']))->format('%R') == '-' &&
                                $timeNow->diff(new \DateTime($arr['to']))->format('%R') == '+'
                        ) {
                            $list->is_online = true;
                        }
                    }
                }
            } else if ($list->open_type == 1) {
                $list->is_online = true;
            }
            $list->media_url =  $this->public_url.$list->avatar;
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'clinic data',
                'data' => $list
            ];
        }
        return response()->json($out, 200, []);
    }

    public function edit(Request $req) {
        $requestData = $req->only([
            'open_type',
            'name',
            'email',
            'avatar',
            'address',
            'street_name',
            'house_number',
            'zip_code',
            'city',
            'providence',
            'county',
            'phone',
            'fax',
            'latitude',
            'longitude',
            'working_hours',
                //'languages',
        ]);
        // upload
        if ($req->hasFile('avatar') == true) {
            $file = upload::store($req->avatar);
            if (is_array($file)) {
                $requestData['avatar'] = $file['data'];
            }
        }
        // end upload

        $update = clinic::edit_data($req->only(['clinic_id']), $requestData);


//        if (strpos($req->speciality, ',') == true) {
//            $speciality_list = explode(",", $req->speciality);
//            foreach ($speciality_list as $value) {
//                SpecialityClinic::addTo($value, $req->clinic_id);
//            }
//        } else {
//            SpecialityClinic::addTo($req->speciality, $req->clinic_id);
//        }
        if ($req->has('specialities')) {
            $specialities = explode(",", $req->specialities);
            if (!empty($specialities)) {
                \DB::table('speciality_clinic')->where('clinic_id', '=', $req->clinic_id)->delete();
                // @todo all specialities ids are valide
                foreach ($specialities as $speciality) {
                    \DB::table('speciality_clinic')->insert(['clinic_id' => $req->clinic_id, 'speciality_id' => $speciality]);
                }
            }
        }
        if ($req->has('languages')) {
            if (!empty($languages)) {
                $languages = explode(",", $req->languages);
                \DB::table('lang_sub')->where('clinic_id', '=', $req->clinic_id)->delete();
                // @todo all languages ids are valide
                foreach ($languages as $lang) {
                    \DB::table('lang_sub')->insert(['clinic_id' => $req->clinic_id, 'lang_id' => $lang]);
                }
            }
        }
        if ($req->has('member_at')) {
            $member_ats = explode(",", $req->member_at);
            if (!empty($member_ats)) {
                \DB::table('clinic_doctor')->where('clinic_id', '=', $req->clinic_id)->delete();
                // @todo all languages ids are valide
                foreach ($member_ats as $member_at) {
                    \DB::table('clinic_doctor')->insert(['clinic_id' => $req->clinic_id, 'doctor_id' => $member_at]);
                }
            }
        }


//        // add lang
//        if (strpos($req->lang_sub, ',') == true) {
//            $lang_list = explode(",", $req->lang_sub);
//            foreach ($lang_list as $value) {
//                LS::addToClinic($req->clinic_id, $value);
//            }
//        } else {
//            LS::addToClinic($req->clinic_id, $req->lang_sub);
//        }
//
//        // add lang
//        if (strpos($req->member_at, ',') == true) {
//            $lang_list = explode(",", $req->member_at);
//            foreach ($lang_list as $value) {
//                ClinicDoctor::addTo($req->clinic_id, $value);
//            }
//        } else {
//            ClinicDoctor::addTo($req->clinic_id, $req->member_at);
//        }


        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'done Editd',
            'data' => $req->all()
        ];
        return response()->json($out, 200, []);
    }

    public function all(Request $req) {
       
        $list = Clinic::all();
//        $list = clinic::get_all();
        foreach ($list as $key => $value) {
            $check = Favo::where('user_id',$req->user_id)->where('to_type',3)->where('to_id', $value->clinic_id)->first();
            $value->isMyFavourite = false;
            if($check)
                $value->isMyFavourite = true;

            $value->media_url = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';

        }
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'Clinics list',
            'data' => $list
        ];
        return response()->json($out, 200, []);
        //return response()->json(["foo" => "bar"], 200, []);
    }

    public function rate(Request $req) {
        $setPar = ["user_id","clinic_id","comment","rate"];
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
        if ($req->rate >= 5) {
            $req['rate'] = 5;
        }
        if ($req->rate <= 0) {
            $req['rate'] = 0;
        }

        $_exis = Rate::where($req->all())->exists();

        if ($_exis == false) {
            $exist_rate_perc = RP::where('clinic_id', '=', $req->clinic_id)->exists();

            $valueNumberRate = substr($req->rate, 0, 1);
            if ($exist_rate_perc == true) {
                RP::where([ 'clinic_id' => $req->clinic_id])->increment('r' . $valueNumberRate, 1);
            } else {
                RP::create($req->only([ 'clinic_id']));
                RP::where([ 'clinic_id' => $req->clinic_id])->increment('r' . $valueNumberRate, 1);
            }

            Rate::create($req->all());

            $results = Rate::select(['rate'])->where('clinic_id', '=', $req->only(['clinic_id']))->get();
            $rate = 0;
            foreach ($results as $row) {
                $rate += $row->rate;
            }
            $getRateNum = $rate / count($results);

            clinic::where([ 'clinic_id' => $req->clinic_id])->update([ 'rate_num' => $getRateNum, 'rate_count' => count($results)]);

            $out = [
                'status' => true, 'type' => 'successful', 'msg' => 'change rate', 'data' => $exist_rate_perc
            ];
        } else {
            $out = [
                'status' => false, 'type' => 'error', 'msg' => 'exists rate', 'data' => []
            ];
        }
        return response()->json($out);
    }

    public function rating_list(Request $req){
        $setPar = ["clinic_id"];
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
        $all_rate = Rate::select('rate.*','users.title','users.first_name','users.last_name','users.avatar')->join('users','users.user_id','rate.user_id')->where('clinic_id',$req->clinic_id)->get();
        foreach ($all_rate as $key => $value) {
            $value->avatar_url = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
        }
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'Rating List',
            'data' => $all_rate
        ];
        return response()->json($out);
    }

}
