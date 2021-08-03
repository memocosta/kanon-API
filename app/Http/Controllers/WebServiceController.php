<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Clinic;
use App\Session;
use App\Voucher;
use App\Favourite as Favo;
use DB;
use Validator;

class WebServiceController extends Controller
{
    
	public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 
    public function all_countries()
    {
        $countries = DB::table('countries')->get();
        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $countries
            ];
        return response()->json($out, 200, []);
    }
    public function get_country(Request $req)
    {
        $country = DB::table('countries')->where('country_phone_code',$req->country_code)->first();
        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $country
            ];
        return response()->json($out, 200, []);
    }
    public function contact_list(Request $req)
    {
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
    	$user_id = $req->user_id;
		$msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
		foreach ($msgs as $key => $value) {
			unset($msgs[$key]->dist);
			unset($msgs[$key]->updated_at);
			unset($msgs[$key]->is_forward);
			unset($msgs[$key]->user_id);

			if($value->from_id == $user_id){
				$data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->first();
			}else{
				$data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->first();
			}
			if($data){
				$value->user_id = $data->user_id;
				$value->title = $data->title;
				$value->first_name = $data->first_name;
				$value->last_name = $data->last_name;
				$value->avatar = $data->avatar;
                $value->available = $data->is_available;
				$value->user_type = $data->user_type;

                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                $open_session = Session::whereIn('user_id', [$user_id ,  $value->user_id ] )->whereIn('other_id', [$user_id ,  $value->user_id ] )->where('type', 0)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;
                $datamsgs[] = $msgs[$key];
			}else{
				unset($msgs[$key]);
			}
		}
		$out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    } 
    public function contact_list_web(Request $req)
    {
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
        $user_id = $req->user_id;
        $msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $msgs2 = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages_clinics` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages_clinics WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
        foreach ($msgs as $key => $value) {
            unset($msgs[$key]->dist);
            unset($msgs[$key]->updated_at);
            unset($msgs[$key]->is_forward);
            unset($msgs[$key]->user_id);

            if($value->from_id == $user_id){
                $data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->first();
            }else{
                $data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->first();
            }
            if($data){
                $value->user_id = $data->user_id;
                $value->title = $data->title;
                $value->first_name = $data->first_name;
                $value->last_name = $data->last_name;
                $value->avatar = $data->avatar;
                $value->available = $data->is_available;
                $value->user_type = $data->user_type;

                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                $open_session = Session::whereIn('user_id', [$user_id ,  $value->user_id ] )->whereIn('other_id', [$user_id ,  $value->user_id ] )->where('type', 0)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;
                $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }
        }

        foreach ($msgs2 as $key => $value) {
            unset($msgs2[$key]->dist);
            unset($msgs2[$key]->updated_at);
            unset($msgs2[$key]->is_forward);

            if($value->from_id == $user_id && $value->from_id == $value->user_id){
                $data = Clinic::where('clinic_id',$value->to_id)->first();
            }else{
                $data = Clinic::where('clinic_id',$value->from_id)->first();
            }
            if($data){
                $value->clinic_id = $data->clinic_id;
                $value->name = $data->name;
                $value->avatar = $data->avatar;
                $value->user_type = 3;

                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                $open_session = Session::where('user_id', $user_id  )->where('other_id',   $value->clinic_id  )->where('type', 1)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;
                $datamsgs[] = $msgs2[$key];
            }else{
                unset($msgs2[$key]);
            }
        }
        $union = array_merge($msgs, $msgs2);
        $result = self::array_sort_by_column($union, 'created_at');

        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $result
            ];
        return response()->json($out, 200, []);
    } 
    public function contact_list_clinic_web(Request $req)
    {
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
        $clinic_id = $req->clinic_id;
        $msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages_clinics` t WHERE ( t.from_id = $clinic_id OR t.to_id = $clinic_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages_clinics WHERE ( from_id = $clinic_id OR to_id = $clinic_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id");
        $datamsgs = array();

        foreach ($msgs as $key => $value) {
            unset($msgs[$key]->dist);
            unset($msgs[$key]->updated_at);
            unset($msgs[$key]->is_forward);

            if($value->from_id == $clinic_id && $value->to_id == $value->user_id){
                $data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->first();

            }else{
                $data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->first();

            }
            if($data){
                $value->user_id = $data->user_id;
                $value->title = $data->title;
                $value->first_name = $data->first_name;
                $value->last_name = $data->last_name;
                $value->avatar = $data->avatar;
                $value->available = $data->is_available;
                $value->user_type = $data->user_type;

                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                $open_session = Session::where('user_id',  $value->user_id )->where('other_id', $clinic_id )->where('type', 1)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;
                $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }
        }

        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    } 
    public function array_sort_by_column(&$array, $column, $direction = SORT_ASC) {
        $reference_array = array();

        foreach($array as $key => $row) {
            $reference_array[$key] = $row->$column;
        }

        array_multisort($reference_array, $direction, $array);
        return $array;
    }
    public function doctors_chat_list(Request $req)
    {
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
    	$user_id = $req->user_id;
		$msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
		foreach ($msgs as $key => $value) {
			unset($msgs[$key]->dist);
			unset($msgs[$key]->updated_at);
			unset($msgs[$key]->is_forward);
			unset($msgs[$key]->user_id);

			if($value->from_id == $user_id){
				$data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->where('user_type',2)->first();
			}else{
				$data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->where('user_type',2)->first();
			}
			if($data){
				$value->user_id = $data->user_id;
				$value->title = $data->title;
				$value->first_name = $data->first_name;
				$value->last_name = $data->last_name;
				$value->avatar = $data->avatar;
				$value->available = $data->is_available;
                $value->user_type = 2;

                $open_session = Session::where('user_id', $user_id )->where('other_id', $value->user_id )->where('type', 0)->orderBy('session_id', 'desc')->first();

                $check = Favo::where('user_id',$req->user_id)->where('to_type',2)->where('to_id', $value->user_id)->first();
                $value->isMyFavourite = false;
                if($check)
                    $value->isMyFavourite = true;
                
                $value->open_session = ($open_session) ? $open_session->status : 0;


    			$value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
    			$value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';

                $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }




		}
		$out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    }

    public function doctors_open_chat_list(Request $req)
    {
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
        $user_id = $req->user_id;
        $msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
        foreach ($msgs as $key => $value) {
            unset($msgs[$key]->dist);
            unset($msgs[$key]->updated_at);
            unset($msgs[$key]->is_forward);
            unset($msgs[$key]->user_id);

            if($value->from_id == $user_id){
                $data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->where('user_type',2)->first();
            }else{
                $data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->where('user_type',2)->first();
            }
            if($data){
                $value->user_id = $data->user_id;
                $value->title = $data->title;
                $value->first_name = $data->first_name;
                $value->last_name = $data->last_name;
                $value->avatar = $data->avatar;
                $value->available = $data->is_available;
                $value->user_type = 2;

                $open_session = Session::where('user_id', $user_id)->where('other_id', $value->user_id )->where('type', 0)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;

                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                
                if ($value->open_session == 0) {
                    unset($msgs[$key]);
                }
                if(isset($msgs[$key]))
                    $datamsgs[] = $msgs[$key];

            }else{
                unset($msgs[$key]);
            }


        }
        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    }
    public function users_chat_list(Request $req)
    {
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
        $user_id = $req->user_id;
        $msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
        foreach ($msgs as $key => $value) {
            unset($msgs[$key]->dist);
            unset($msgs[$key]->updated_at);
            unset($msgs[$key]->is_forward);
            unset($msgs[$key]->user_id);

            if($value->from_id == $user_id){
                $data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->where('user_type',1)->first();
            }else{
                $data = User::leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->where('user_type',1)->first();
            }
            if($data){
                $value->user_id = $data->user_id;
                $value->title = $data->title;
                $value->first_name = $data->first_name;
                $value->last_name = $data->last_name;
                $value->avatar = $data->avatar;
                $value->available = $data->is_available;
                $value->user_type = 2;

                $open_session = Session::where('user_id', $user_id )->where('other_id', $value->user_id )->where('type',0)->orderBy('session_id', 'desc')->first();

                $check = Favo::where('user_id',$req->user_id)->where('to_type',2)->where('to_id', $value->user_id)->first();
                $value->isMyFavourite = false;
                if($check)
                    $value->isMyFavourite = true;
                
                $value->open_session = ($open_session) ? $open_session->status : 0;


                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';

                $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }




        }
        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    }
    public function clinics_chat_list(Request $req)
    {
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
    	$user_id = $req->user_id;
		$msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages_clinics` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages_clinics WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
		foreach ($msgs as $key => $value) {
			unset($msgs[$key]->dist);
			unset($msgs[$key]->updated_at);
			unset($msgs[$key]->is_forward);
			unset($msgs[$key]->user_id);


			if($value->from_id == $user_id){
				$data = Clinic::where('clinic_id',$value->to_id)->first();
			}else{
				$data = Clinic::where('clinic_id',$value->from_id)->first();
			}
			if($data){
				$value->clinic_id = $data->clinic_id;
				$value->user_id = $data->user_id;
				$value->name = $data->name;
				$value->rate = $data->rate_num;
				$value->working_hours = $data->working_hours;
				$value->avatar = $data->avatar;
                $value->user_type = 3;

                $open_session = Session::where('user_id', $user_id )->where('other_id', $value->clinic_id )->where('type', 1)->orderBy('session_id', 'desc')->first();
                $value->open_session = ($open_session) ? $open_session->status : 0;

                $check = Favo::where('user_id', $user_id)->where('to_type',3)->where('to_id', $value->clinic_id)->first();
                $value->isMyFavourite = false;
                if($check)
                    $value->isMyFavourite = true;

			$value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
			$value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
            $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }


		}
		$out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    }

    public function clinics_open_chat_list(Request $req)
    {
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
        $user_id = $req->user_id;
        $msgs = \DB::select("SELECT * , ( r.from_id + r.to_id ) AS dist FROM ( SELECT * FROM `chat_massages_clinics` t WHERE ( t.from_id = $user_id OR t.to_id = $user_id) ORDER BY t.massage_id DESC ) r JOIN (SELECT MAX(massage_id) massage_id, ( from_id + to_id ) AS dist FROM chat_massages_clinics WHERE ( from_id = $user_id OR to_id = $user_id) GROUP BY dist ORDER BY massage_id DESC) t2 ON r.massage_id = t2.massage_id  ");
        $datamsgs = array();
        foreach ($msgs as $key => $value) {
            unset($msgs[$key]->dist);
            unset($msgs[$key]->updated_at);
            unset($msgs[$key]->is_forward);
            unset($msgs[$key]->user_id);


            if($value->from_id == $user_id){
                $data = Clinic::where('clinic_id',$value->to_id)->first();
            }else{
                $data = Clinic::where('clinic_id',$value->from_id)->first();
            }
            if($data){
                $value->clinic_id = $data->clinic_id;
                $value->user_id = $data->user_id;
                $value->name = $data->name;
                $value->rate = $data->rate_num;
                $value->working_hours = $data->working_hours;
                $value->avatar = $data->avatar;
                $value->user_type = 3;

                $open_session = Session::where('user_id', $user_id  )->where('other_id', $value->clinic_id )->where('type', 1)->orderBy('session_id', 'desc')->first();
                
                $value->open_session = ($open_session) ? $open_session->status : 0;


                $value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
                $value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';
                if ($value->open_session == 0) {
                    unset($msgs[$key]);
                }
                if(isset($msgs[$key]))
                    $datamsgs[] = $msgs[$key];
            }else{
                unset($msgs[$key]);
            }

        }
        $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $datamsgs
            ];
        return response()->json($out, 200, []);
    }


    public function single_chat(Request $req)
    {
    	$setPar = ["user_id","other_id"];
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
        
         //Case => chat with Admin
        if ($req->other_id == 0) {
            $user = user::where('user_type', '=', 0)->first();
            $req->other_id = $user['user_id'];
        }

    	$user_id = $req->user_id;
		$msgs = \DB::table('chat_massages')->whereIn('chat_massages.from_id',[$req->user_id,$req->other_id])
			->whereIn('chat_massages.to_id',[$req->user_id,$req->other_id])
			->orderBy('massage_id','DESC')
			->get();
		foreach ($msgs as $key => $value) {
			unset($msgs[$key]->dist);
			unset($msgs[$key]->updated_at);
			unset($msgs[$key]->is_forward);
			unset($msgs[$key]->user_id);

			if($value->from_id == $user_id){
				$data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->to_id)->where('activated',1)->first();
			}else{
				$data = User::select('*','users.user_id as user_id')->leftJoin('doctors', 'doctors.user_id', '=', 'users.user_id')->where('users.user_id',$value->from_id)->where('activated',1)->first();
			}
			if($data){
				$value->user_id = $data->user_id;
				$value->title = $data->title;
				$value->first_name = $data->first_name;
				$value->last_name = $data->last_name;
				$value->avatar = $data->avatar;
				$value->available = $data->is_available;
                $value->user_type = $data->user_type;

			}else{
				unset($msgs[$key]);
			}
			$value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
			$value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';

		}
		$out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $msgs
            ];
        return response()->json($out, 200, []);
    }
    public function single_chat_clinic(Request $req)
    {
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
    	$user_id = $req->user_id;
		$msgs = \DB::table('chat_massages_clinics')->whereIn('chat_massages_clinics.from_id',[$req->user_id,$req->clinic_id])
			->whereIn('chat_massages_clinics.to_id',[$req->user_id,$req->clinic_id])
			->orderBy('massage_id','DESC')
			->get();
		foreach ($msgs as $key => $value) {
			unset($msgs[$key]->dist);
			unset($msgs[$key]->updated_at);
			unset($msgs[$key]->is_forward);
			unset($msgs[$key]->user_id);
			// echo $value->avatar;
			

			if($value->from_id == $user_id){
				$data = Clinic::where('clinic_id',$value->to_id)->first();
			}else{
				$data = Clinic::where('clinic_id',$value->from_id)->first();
			}
			if($data){
				$value->clinic_id = $data->clinic_id;
				$value->user_id = $data->user_id;
				$value->name = $data->name;
				$value->rate = $data->rate_num;
				$value->working_hours = $data->working_hours;
				$value->avatar = $data->avatar;
                $value->user_type = 3;

			}else{
				unset($msgs[$key]);
			}
			$value->media_url_avatar = (!empty($value->avatar)) ? $this->public_url.$value->avatar : '';
			$value->media_url_image = (!empty($value->image)) ? $this->public_url.$value->image : '';

		}
		$out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $msgs
            ];
        return response()->json($out, 200, []);
    }

    public function contact_phone(Request $req){
    	$setPar = ["contact_phone"];
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
        $arr = explode(',',$req->contact_phone);
        $data = User::whereIn('phone_hash',$arr)->get();
         $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $data
            ];
        return response()->json($out, 200, []);

    }

    public function open_session(Request $req){
        $requestData = $req->all();
        
        $rules = array(
            'type' => 'required|numeric',
            'other_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'payment_type' => 'required|numeric',
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
            $req['status'] = 1;
            $req['track_id'] = (isset($req->track_id)) ? $req->track_id : '';
            if($req->payment_type == 2) {
                $voucher = Voucher::where('voucher',$req['track_id'])->where('status',1)->first();
                if(!$voucher) {
                   $out = [
                        'status' => false,
                        'type' => 'Error',
                        'msg' => 'voucher track_id not Found',
                        'data' => []
                    ];
                    return response()->json($out, 200, []);
                    exit();
                }
            }
            Session::where('user_id', $req->user_id )->where('other_id', $req->other_id )->where('type', $req->type)->update(['status'=>0]);
            $save = new Session;
            $save->user_id =  $req->user_id ;
            $save->other_id =  $req->other_id ;
            $save->status =  $req->status ;
            $save->type =  $req->type ;
            $save->track_id =  $req->track_id ;
            $save->payment_type =  $req->payment_type ;
            $save->save();
            $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => $save
            ];
        }
        return response()->json($out, 200, []);

    }

    public function check_session(Request $req){
        $requestData = $req->all();
        
        $rules = array(
            'type' => 'required|numeric',
            'other_id' => 'required|numeric',
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
            $open_session = Session::whereIn('user_id', [$req->user_id , $req->other_id] )->whereIn('other_id', [$req->user_id , $req->other_id] )->where('type', $req->type)->where('status',1)->orderBy('session_id', 'desc')->first();
                
            if ($open_session) {
                $out = [
                    'status' => true,
                    'type' => 'success',
                    'msg' => 'success',
                    'data' => $open_session
                ];
            } else {
                $out = [
                    'status' => false,
                    'type' => 'Error',
                    'msg' => 'You need to open the session',
                    'data' => []
                ];
            }
        }
        return response()->json($out, 200, []);

    }
    public function close_session(Request $req){
        $requestData = $req->all();
        $rules = array(
            'session_id' => 'required|numeric',
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
            Session::where('session_id', $req->session_id )->update(['status'=>0]);
            $out = [
                'status' => true,
                'type' => 'success',
                'msg' => 'success',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);

    }
}

