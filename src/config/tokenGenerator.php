<?php

class JWT {
    public static function createToken($token, $key)
    {
        $headers = ['alg'=>'HS256', 'typ'=>'JWT'];
        
        $encoded_headers = base64_encode(json_encode($headers));
        $encoded_payload = base64_encode(json_encode($token));
        
        $signature = hash_hmac('SHA256', "$encoded_headers.$encoded_payload", $key, true);
        $encoded_signature = base64_encode($signature);

        $jwt = $encoded_headers.".".$encoded_payload.".".$encoded_signature;

        return $jwt;
    }

    public static function validate($token, $key) {

        $jwt_values = explode(".", $token);

        $recieved_signature = $jwt_values[2];
        $recieved_header_payload = $jwt_values[0].'.'.$jwt_values[1];

        $arrExpires = json_decode(base64_decode($jwt_values[1], true));
        
        $expires = $arrExpires->ends;
        
        $now = new DateTime();
        $now = $now->getTimeStamp();
        
        $resultedSignature = base64_encode(hash_hmac('SHA256', $recieved_header_payload, $key, true));

        if ($expires > $now && $resultedSignature === $recieved_signature) {
            return true;
        } else {
            return false;
        }
    }
}