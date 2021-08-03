<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Speciality;

class SpecialityController extends Controller
{
    public function all (Request $req)
    {
        $list = Speciality::get();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list specialitys',
            'data' => $list
        ];
        return response()->json($out, 200, []);
    }
}