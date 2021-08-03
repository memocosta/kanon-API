<?php
namespace App\Http\Controllers;

use \App\Token as token;

class FirebaseController extends Controller
{
    public static function push ($token, $notification, $body = false)
    {
        /*if (token::isIos($token)){
            unset($body['click_action']);
            $body['content_available'] = true;
        }*/
        
        #API access key from Google API's Console
        $API_ACCESS_KEY = 'AAAAChQqRaY:APA91bHH8OINlyqTzERlgGs4ul3sN5N3MpJeVOsq28Wv7bZksAPrELrWzdx3Mx44L9_GFBvgvtWrvtLhuHTlnB99lwbG3hvuS8EbvNR_rMV75Kg-hM0RVDNhcx9m5ye9xFUR95Tcq9bL7KdYyAYrlRzHdG3Pt9cE7w';
        #prep the bundle
        if ( $body == false ) {
            $fields = [
                'to' => $token,
                'notification' => $notification,
//                'content_avaialable' => true,
                "priority" => "high"
            ];
        } else {
            $fields = [
                'to' => $token,
                'notification' => $body,
//                'content_avaialable' => true,
                "priority" => "high",
                'data' => $body
            ];
        }
        $headers = [
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        ];
        #Send Reponse To FireBase Server	
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        //echo $result;
        return $result;
    }
}