<?php

namespace App\Http\Controllers;

use App\Http\Controllers\uploadsController as upload;
use Illuminate\Http\Request;
use \App\Doctor as doctor;
use \App\User as user;
use \App\Rate;
use \App\RatePercentage;
use \App\SpecialityDoc;
use \App\Speciality;
use \App\LangSub;
use \App\ClinicDoctor;
use \App\Lang;
use \App\Favourite as Favo;
use Illuminate\Support\Facades\Input;
use Validator;

class DoctorsController extends Controller {

    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 
    public function add(Request $req) {
        $out = [
            'status' => false,
            'type' => 'Oops!',
            'msg' => 'not add doctor',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    // update
    public function update(Request $req) {
        // Parameters Error
        $getPar = array_keys($req->all());
        $setPar = [
            "doc_id",
            "address",
            "supported_lang"
        ];
        if ($getPar !== $setPar) {
            $out = [
                'status' => false,
                'type' => 'Oops',
                'msg' => 'Parameters Error',
                'data' => []
            ];
            return response()->json($out, 200, []);
            exit;
        }
        ///////////////////////////////////////
        doctor::where([ 'user_id' => $req->doc_id])->update($req->only(['address', 'supported_lang']));
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'updated data',
            'data' => []
        ];
        return response()->json($out, 200, []);
    }

    // list
    public function all(Request $req) {
        
        $results = doctor::join_user();
        foreach ($results as $key => $value) {
            $check = Favo::where('user_id',$req->user_id)->where('to_type',2)->where('to_id', $value->user_id)->first();
            $value->isMyFavourite = false;
            if($check)
                $value->isMyFavourite = true;
            $value->media_url = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
        }
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'Doctors list',
            'data' => $results
        ];

        return response()->json($out, 200, []);
    }

    public function another() {
//        $results = doctor::join('users', 'doctors.user_id', '=', 'users.user_id')
//            ->select([
//                'users.user_id',
//                'doctors.user_id',
//                'doctors.rate_num',
//                'doctors.rate_percentage',
//                'doctors.address',
//                'doctors.supported_lang',
//                'doctors.is_available',
//                'doctors.avatar',
//                'users.first_name',
//                'users.last_name',
//                'users.email',
//                'users.phone'
//            ])
//            ->get();
//        return response()->json($results);
    }

    public function rate(Request $req) {
        $setPar = ["user_id","doctor_id","comment","rate"];
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
            $exist_rate_perc = RatePercentage::where('doctor_id', '=', $req->doctor_id)->exists();

            $valueNumberRate = substr($req->rate, 0, 1);
            if ($exist_rate_perc == true) {
                RatePercentage::where([ 'doctor_id' => $req->doctor_id])->increment('r' . $valueNumberRate, 1);
            } else {
                RatePercentage::create($req->only([ 'doctor_id']));
                RatePercentage::where([ 'doctor_id' => $req->doctor_id])->increment('r' . $valueNumberRate, 1);
            }

            Rate::create($req->all());

            $results = Rate::select(['rate'])->where('doctor_id', '=', $req->only(['doctor_id']))->get();
            $rate = 0;
            foreach ($results as $row) {
                $rate += $row->rate;
            }
            $getRateNum = $rate / count($results);

            doctor::where([ 'user_id' => $req->doctor_id])->update([ 'rate_num' => $getRateNum, 'rate_count' => count($results)]);

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'change rate',
                'data' => $exist_rate_perc
            ];
        } else {
            $out = [
                'status' => false,
                'type' => 'error',
                'msg' => 'exists rate',
                'data' => []
            ];
        }
        return response()->json($out);
    }

    public function rating_list(Request $req){
        $setPar = ["doctor_id"];
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
        $all_rate = Rate::select('rate.*','users.title','users.first_name','users.last_name','users.avatar')->join('users','users.user_id','rate.user_id')->where('doctor_id',$req->doctor_id)->get();
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

    public function change_status(Request $req) {
        $input = Input::all();
        $rules = array(
            'is_available' => 'required',
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
            return response()->json($out, 200, []);
            exit;
        } 
        $setPar = ["user_id","is_available"];
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
        doctor::where('user_id', '=', $req->user_id)->update(['is_available' => $req->is_available]);
        $user = doctor::where('user_id', '=', $req->user_id)->first();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'change status',
            'data' => $user
        ];
        return response()->json($out);
    }

    public function change_speciality(Request $req){
        $input = Input::all();
        $rules = array(
            'speciality_id' => 'required',
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
            return response()->json($out, 200, []);
            exit;
        } 
        $setPar = ["user_id","speciality_id"];
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
        SpecialityDoc::where('doc_id', '=', $req->user_id)->delete();
        $arr = explode(',', $req->speciality_id);
        foreach ($arr as $key => $value) {
            SpecialityDoc::insert(['doc_id'=> $req->user_id , 'speciality_id' => $value]);
        }
        $data = Speciality::whereIn('id',$arr)->get();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'change speciality',
            'data' => $data
        ];
        return response()->json($out);
    }

    public function change_language(Request $req){
        $input = Input::all();
        $rules = array(
            'language_id' => 'required',
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
            return response()->json($out, 200, []);
            exit;
        }
        $setPar = ["user_id","language_id"];
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
        LangSub::where('user_id', '=', $req->user_id)->delete();
        $arr = explode(',', $req->language_id);
        foreach ($arr as $key => $value) {
            LangSub::insert(['user_id'=> $req->user_id , 'lang_id' => $value]);
        }
        $data = Lang::whereIn('lang_id',$arr)->get();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'change speciality',
            'data' => $data
        ];
        return response()->json($out);
    }

    public function leave_clinic(Request $req){
        $input = Input::all();
        $rules = array(
            'clinic_id' => 'required|numeric',
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
            return response()->json($out, 200, []);
            exit;
        }
        $setPar = ["user_id","clinic_id"];
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
        ClinicDoctor::where('doctor_id', '=', $req->user_id)->where('clinic_id', '=', $req->clinic_id)->delete();
        
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'Leaved Clinic Successfully',
            'data' => []
        ];
        return response()->json($out);
    }

    public function invite_clinic(Request $req){
        $input = Input::all();
        $rules = array(
            'clinic_id' => 'required|numeric',
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
            return response()->json($out, 200, []);
            exit;
        }
        $setPar = ["user_id","clinic_id"];
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
        $check = ClinicDoctor::where('doctor_id', '=', $req->user_id)->where('clinic_id', '=', $req->clinic_id)->first();
        if($check){
             $out = [
                    'status' => false,
                    'type' => 'dd',
                    'msg' => 'Already Added',
                    'data' => []
                ];
        }else{
            $new = new ClinicDoctor;
            $new->doctor_id=$req->user_id;
            $new->clinic_id =  $req->clinic_id;
            $new->save();
            
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'invited to Clinic Successfully',
                'data' => []
            ];
        }
        return response()->json($out);
    }

}
