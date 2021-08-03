<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class uploadsController extends Controller {

    protected static $extensions = [
        'aif',
        'iff',
        'm3u',
        'm4a',
        'mid',
        'mp3',
        'mpa',
        'wav',
        'wma',
        '3g2',
        '3gp',
        'asf',
        'avi',
        'flv',
        'm4v',
        'mov',
        'mp4',
        'mpg',
        'srt',
        'swf',
        'vob',
        'wmv',
        'jpg',
        'jpeg',
        'png',
        'gif',
    ];

    //
    public static function store($image) {
//        if (in_array($image->getClientOriginalExtension(), self::$extensions)) {
        if (1) {

            // make file name
            $filename = md5(time()) . '.' . $image->getClientOriginalExtension();
            // move file to dir uploads
            $image->move('uploads', $filename);
            // return path file
//            $filename = 'public/uploads/' . $filename;
            $out = [
                'status' => true,
                'msg' => true,
                'data' => $filename,
            ];
        } else {
            $out = [
                'status' => false,
                'msg' => false,
                'data' => '',
            ];
        }
        return $out;
    }

    //
    public function up(Request $req) {
        if ($req->hasFile('image') == true) {
            $up = self::store($req->image);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'uploaded file',
                'data' => $up
            ];
        } else {
            $out = [
                'status' => false,
            ];
        }
        return response()->json($out, 200, []);
    }

}
