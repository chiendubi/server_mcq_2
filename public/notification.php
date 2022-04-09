<?php
header('Access-Control-Allow-Origin: *');
define("FCM_SERVER_KEY", 'AAAA_mAS_M8:APA91bEs2K-bQ6andU-4L4Wzz77DkJwEjatIcdQsaiMzLhTBev1MM7u7tZtS0j8JYYqi-ninQ5gpVBLZkLAYBJ8pMwLwzJumcTSzXVEGYWKgb9t79UvcvEF4CWFMkcQlpq1zDntIAVZ4');
class Notification{
    public static function send_notification_fcm($user_id, $title, $message, $data = array()){
        $sql_model = new VanillaModel();
        $result = null;
        // if (LOCAL_DATA){
        //     $email = 'lethanhluan54cntt@gmail.com';  
        //     $device_token = 'c06Gjt4BMpk:APA91bHbuHsu2t6w--suKXsPj6D4cuNALLnQxyQUvvRgmwKq-05BV6YTHMBbwAGxyyrMXsU2CCd8FPosU2Ce0z3rkxIVgRdo8_z5OPAG_ItnO4ynq_leyqlwIu2PDPCYEn9yP4-YhkUm';
        //     $push_result = Notification::config_notification_fcm($device_token, $title, $message, $data);
        // }else{
            $fbuser = $sql_model->queryWithOneResultSet('SELECT fcm_token FROM users WHERE id = "' . $user_id . '"');
            // // logErrorDebug('sendPushFCM() - $token = ' . print_r($fbuser, true));
            // logErrorDebug('fcm_token() - $token = ' . print_r($fbuser['fcm_token'], true));
            if ($fbuser && $fbuser['fcm_token'] != '') {
                $device_token = $fbuser['fcm_token']; 
                // $device_token = "exG-h7ra9kvhgYC3yPyY8i:APA91bE2MkkeQaILQX14BBlanjSQpduQ-9qcDNAt6VIDnRGWzWrwn6rbWpvV18b7m7w6fdVmbuElkbUx5GQaKjlntHhEwy_v33Iv5WoN_XNTufbU7TD_w_gEQvOJMUaaAxFeVx5_3W0f";
                $push_result = Notification::config_notification_fcm($device_token, $title, $message, $data);
            }
        // }
        // logErrorDebug('sendPushFCM() - $token = ' . print_r($push_result, true));
        if (isset($push_result)){
            $result = array('status' => 'OK', 'message' => 'Sent Notification');     
        }else{
            return $result; 
        }
        // if ($result) {
        //     // add a new message
        //     $res = $sql_model->queryWithStatus('INSERT INTO messages (cdate,email,title, message) VALUES (NOW(), "' . $user_id . '","' . $title . '","'. $message .'")');
        //     if ($res)
        //         $result['message_id'] = $res['info']['id'];
        //     else
        //         $result['message_id'] = 0;
        // }
        // return $result;
    }
    private static function config_notification_fcm($token, $title, $message, $fcm_data){
        // #prep the bundle     
        // $token = 'eoBpmMZCEio:APA91bE7ziXPtIyLD4sUjdmDel-7o54bLDYUNKNBuoTNqD_a4XQ_VMxsvqur2Mb5IgC98JyXAkCM9jW4L_-XkGtBOoxGGCty5Kq9176Jo4csw8f5IpasAstbYMdCcu2Z67iRN5VJmLSx';
        // $title = 'test';
        // $message = 'test'; 
        $msg = array(
            'title' => $title,
            'body'  => $message,
            'badge' => 1,
            'icon'  => 'myicon',    /*Default Icon*/
            'sound' => 'mySound',   /*Default sound*/
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            // 'content-available' => 0,
            // 'style' => 'inbox',
            // 'summaryText' => 'There are %n% notifications',
            // 'notId': 1
        );

        $data = $fcm_data;
        // $data["click_action"] =  "FLUTTER_NOTIFICATION_CLICK";
        // Validate FCM registration ID
        $headers = array(
            'Authorization: key=' . FCM_SERVER_KEY,
            'Content-Type: application/json'
        );

        // // logErrorDebug('sendPushFCM() - $token = ' . print_r($token, true));
        // // logErrorDebug('sendPushFCM() - $msg = ' . print_r($msg, true));
        // // logErrorDebug('sendPushFCM() - $headers = ' . print_r($headers, true));

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://iid.googleapis.com/iid/info/' . $token . '?details=true' );
        curl_setopt( $ch,CURLOPT_POST, false );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        $result1 = curl_exec($ch );
        $result2 = json_decode($result1, true);

        // // logErrorDebug('sendPushFCM() - $result2 = ' . print_r($result2, true));

        // Check platform
        $platform = $result2['platform'];
        if ($platform == "ANDROID") {
            // for Android
            $fields = array(
                'to'  => $token,
                'notification' => $msg,
                'data' => $data,
            );
        } else if ($platform == "IOS") {
            // for iOS
            $fields = array(
                'to'  => $token,
                'notification' => $msg,
                'data' => $data,
            );
        }
    
        $headers = array(
            'Authorization: key=' . FCM_SERVER_KEY,
            'Content-Type: application/json'
        );

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
        return $result;
    }
}
?>