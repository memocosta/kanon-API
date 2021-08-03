<?php

namespace App\Http\Controllers;

use App\Http\Controllers\uploadsController as upload;
use Illuminate\Http\Request;
use \App\Document;
use \App\User as user;
use \App\Clinic;
use Validator;
use Illuminate\Routing\UrlGenerator;

class DocumentsController extends Controller {

    public function __construct() {
        $this->public_url = url('/').'/uploads/';
    } 
    public function add(Request $req) {
        // upload
        if ($req->hasFile('image') == true) {
            $file = upload::store($req->image)['data'];
        } else {
            $file = '';
        }
        // end upload

        date_default_timezone_set('UTC');

        $requestData = $req->all();
        $requestData['image'] = $file;
        $document = Document::create($requestData);
        Document::where('document_id', '=', $document['id'])->update(['created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);


        $document['document_id'] = $document['id'];
        $document['privacy'] = 0;
        $document['media_url'] = $this->public_url.$document['image'];
        unset($document['id']);

        $out = [
            'status' => true,
            'type' => 'successful',
            'msg' => 'added document',
            'data' => $document
        ];
        return response()->json($out, 200, []);
    }

    public function get(Request $req) {
        $requestData = $req->all();
        if(isset($req->user_id)){
            $token_exist = user::where($req->only(['user_id']))->exists();
        }
        elseif(isset($req->clinic_id)){
            $token_exist = Clinic::where($req->only(['clinic_id']))->exists();
        }
        else{
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        }
        
        if ($token_exist == false) {
            $out = [
                'status' =>  false,
                'type' => 'Error',
                'msg' => 'user id is wrong',
                'data' => []
            ];
        } else{
            if(isset($req->user_id)){
                $documents = Document::where('user_id', '=', $req->user_id)->get();
            }elseif(isset($req->clinic_id)){
                $documents = Document::where('clinic_id', '=', $req->clinic_id)->get();

            }
            foreach ($documents as $key => $value) {
                $value->document_id = (int) $value->document_id;
                $value->privacy = (int) $value->privacy;
                $documents[$key]->media_url = (!empty($value->image)) ? $this->public_url.$value->image : '';
            }

            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'All my document',
                'data' => $documents
            ];
        }
        return response()->json($out, 200, []);

    }

    public function privacy(Request $req) {
        $requestData = $req->all();

        $token_exist = Document::where('document_id', '=', $req->document_id)->exists();
        
        $rules = array(
            'document_id' => 'required|numeric',
            'privacy' => 'required|numeric',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        }else if ($token_exist == false) {
            $out = [
                'status' =>  false,
                'type' => 'Error',
                'msg' => 'Document id is wrong',
                'data' => []
            ];
        } else{
            Document::where('document_id', '=', $req->document_id)->update(['privacy' => $req->privacy]);
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'privacy updated',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }
    public function single(Request $req) {
        $requestData = $req->all();

        $token_exist = Document::where('document_id', '=', $req->document_id)->exists();
        
        $rules = array(
            'document_id' => 'required|numeric',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        }else if ($token_exist == false) {
            $out = [
                'status' =>  false,
                'type' => 'Error',
                'msg' => 'Document id is wrong',
                'data' => []
            ];
        } else{

            $documents = Document::where('document_id', '=', $req->document_id)->first();
            $documents->media_url = $this->public_url.$documents->image;
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'document details',
                'data' => $documents
            ];
        }
        return response()->json($out, 200, []);
    }
    public function delete(Request $req) {
        $requestData = $req->all();

        $token_exist = Document::where('document_id', '=', $req->document_id)->exists();
        
        $rules = array(
            'document_id' => 'required|numeric',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        }else if ($token_exist == false) {
            $out = [
                'status' =>  false,
                'type' => 'Error',
                'msg' => 'Document id is wrong',
                'data' => []
            ];
        } else{

            $documents = Document::where('document_id', '=', $req->document_id)->delete();
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'document Deleted',
                'data' => []
            ];
        }
        return response()->json($out, 200, []);
    }
    public function update(Request $req) {
        $requestData = $req->all();
        
        $rules = array(
            'type' => 'required',
            'document' => 'required',
            'privacy' => 'required|numeric',
        );
        
        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            $out = [
                'status' => false,
                'type' => 'Error',
                'msg' => $validator->messages(),
                'data' => []
            ];
        } else{
            $requestData = $req->only([
                'type',
                'document',
                'privacy'
            ]);
            Document::where('document_id', '=', $req->document_id)->update($requestData);
            $document = Document::where('document_id', '=', $req->document_id)->first();
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'Document updated',
                'data' => $document
            ];
        }
        return response()->json($out, 200, []);
    }
    public function search(Request $req) {
        $requestData = $req->all();
        
        $rules = array(
            'search' => 'required',
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
        } else{
            $document = Document::where('document', 'like','%'.$req->search.'%')->where('user_id',$req->user_id)->get();
            foreach ($document as $key => $value) {
                $value->media_url = (!empty($value->image)) ? $this->public_url.$value->image : '';
            }
            $out = [
                'status' => true,
                'type' => 'successful',
                'msg' => 'Search Done',
                'search_word' => $req->search,
                'data' => $document
            ];

        }
        return response()->json($out, 200, []);
    }

}
