<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Lang;
use \App\LangSub;

class LangController extends Controller
{
    public function all (Request $req)
    {
        $list = Lang::get();
        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'list langs',
            'data' => $list
        ];
        return response()->json($out, 200, []);
    }
}