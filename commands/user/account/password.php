<?php
class password {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'forgetPassword':
                $this->forgetPassword();
                break;
            case 'changePassword':
                $this->changePassword();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action');
                _json_echo('signin', $response);
        }
    }

    function forgetPassword() {
        $response = array('status' => 'ERROR', 'message' => 'none', 'data'=>array());
        $data = Utility::processedData();
        $username = $data['username'];
        $type = $data['type'];
        $user = $this->sql_model()->queryWithOneResultSet('
            SELECT id FROM users 
            WHERE email = "'. $username . '" OR phone = "'. $username . '"
        ');
        if (!$user) {
            $response['status'] = 'ERROR';
        } else {
            $new_password = substr(str_shuffle(str_repeat("0123456789", 6)), 0, 6);
            $user = $this->sql_model()->queryWithStatus('
                UPDATE users SET password= "'. hash('sha256', $new_password) .'" 
                WHERE id = "'. $user['id'] . '"
            ');
            if($user['status'] == "OK"){
                $response['status'] = 'OK';
                if($type == "type_email"){
                    Email::send_mail_for_password($username, $new_password);
                }else{
                    Sms::send_sms_for_password($username, $new_password);
                }
            }
        }
        _json_echo('forgetPassword', $response);
    }
    
    function changePassword(){
        $response = array('status' => 'ERROR', 'message' => 'changePassword', 'data'=>array());
        $data = Utility::processedData();
        $user_id = $data['id'];
        $old_password = $data['oldPassword'];
        $new_password = $data['newPassword'];
        $user = $this->sql_model()->queryWithOneResultSet('
            SELECT id FROM users 
            WHERE (customer_id = "'. $user_id . '" OR employee_id = "'. $user_id . '") AND password = "'.hash('sha256', $old_password).'"
        ');
        if (empty($user)) {
            $response['status'] = 'ERROR';
        } else {
            $user = $this->sql_model()->queryWithStatus('
                UPDATE users SET password= "'. hash('sha256', $new_password) .'" 
                WHERE id = "'. $user['id'] . '"
            ');
            if($user['status'] == "OK"){
                $response['status'] = 'OK';
            }
        }
        _json_echo('changePassword', $response);
    }

    function __destruct() {
    }
}
