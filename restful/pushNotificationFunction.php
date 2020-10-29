<?php
function sendPushNotificationToFCM($deviceId,$msg) {
//    define('API_ACCESS_KEY','AAAAljCYHsw:APA91bHporL9CZPqShAmHjW8hBvOiIj5veeVj_oRC3Yac3bSy33BFA0_ken5kEdbGLuHgo-Eo4g31CXgFRTKWsmVbli-A1ePcsQI2ArL9RLu1Y2lHGhiqqdTFdz93ndCj4kFksYmJ9yJ');
        $fields = 
        [
           'to'  => $deviceId,
            'priority' =>'high',
           'data'=> $msg
        ];

        $headers = 
        [
          'Authorization: key=AAAAljCYHsw:APA91bHporL9CZPqShAmHjW8hBvOiIj5veeVj_oRC3Yac3bSy33BFA0_ken5kEdbGLuHgo-Eo4g31CXgFRTKWsmVbli-A1ePcsQI2ArL9RLu1Y2lHGhiqqdTFdz93ndCj4kFksYmJ9yJ',
          'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        return $result;
}

