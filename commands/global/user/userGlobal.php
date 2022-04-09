<?php
class userGlobal {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'setUserPermission':
                $this->setUserPermission();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('userGlobal', $response);
        }
    }
    function setUserPermission(){
        $response = array('status' => 'ERROR', 'message' => 'setUserPermission', 'data' => array());
        $data = Utility::processedData();
        $user_id = $data['user_id'];
        $permission = $data['permission'];
        $app_permission_customer = '';
        $app_permission_employee = '';
        if(isset($permission['app_permission_customer'])){
            $app_permission_customer = json_encode($permission['app_permission_customer'], true);
        }
        if(isset($permission['app_permission_employee'])){
            $app_permission_employee = json_encode($permission['app_permission_employee'], true);
        }
        $user = $this->sql_model()->queryWithStatus("
            UPDATE users 
            SET
            app_permission_customer = '".$app_permission_customer."',
            app_permission_employee = '".$app_permission_employee."'
            WHERE id = ".$user_id."
        ");
        if ($user['status'] == 'OK') {
            $response['status'] = 'OK';
        } else {
            $response['message'] = 'System error. Please try again.';
        }
        _json_echo('setUserPermission', $response);
    }

    public static function processUserCheck($controller, $email, $country_pcode, $phone, $id, $tax_code){
        $sql_model = new VanillaModel();
        $error_email = false;
        $error_phone = false;
        $error_tax_code = false;
        $condition = '';
        $returnData = array(
            'id' => $id,
            'email' => $email,
            'country_pcode1' => $country_pcode,
            'phone1' => $phone,
            'tax_code' => $tax_code
        );
        $fieldErrors = array(
            "fieldError" => false,
            "dataError" => array()
        );
        if($id > 0){
            $condition = 'AND id != "' . $id .'"';
        }
        if(!empty($email)){
            $email_check = $sql_model->queryWithOneResultSet('SELECT * FROM customers WHERE email = "' . $email . '"'.$condition);
            if ($email_check) {
                $error_email = true;
            } else {
                $email_check = $sql_model->queryWithOneResultSet('SELECT * FROM employees WHERE email = "' . $email . '"'.$condition);
                if($email_check){
                    $error_email = true;
                }
            }
        }
        if(!empty($phone) && !empty($country_pcode)){
            $phone_check = $sql_model->queryWithOneResultSet('SELECT * FROM customers WHERE country_pcode1="'.$country_pcode.'" AND phone1 = "' . $phone . '"'.$condition);
            if ($phone_check) {
                $error_phone = true;
            } else {
                $phone_check = $sql_model->queryWithOneResultSet('SELECT * FROM employees WHERE country_pcode1="'.$country_pcode.'" AND phone1 = "' . $phone . '"'.$condition);
                if($phone_check){
                    $error_phone = true;
                }
            }
        }
        ## Check duplicate for tax_code.
        if(!empty($tax_code)){
            $tax_code_check = $sql_model->queryWithOneResultSet('SELECT * FROM customers WHERE tax_code = "' . $tax_code . '"'.$condition);
            if ($tax_code_check) {
                $error_tax_code = true;
            }
        }

        if($error_email){
            $fieldErrors['fieldError'] = true;
            $fieldErrors['dataError'][] = array(
                "name" => $controller.".email", 
                "status" => 'EMAILEXIST',
                'returnData' => $returnData
            );
        }
        if($error_phone){
            $fieldErrors['fieldError'] = true;
            $fieldErrors['dataError'][] = array(
                "name" => $controller.".phone1", 
                "status" => 'PHONEEXIST',
                'returnData' => $returnData
            );
        }
        if($error_tax_code){
            $fieldErrors['fieldError'] = true;
            $fieldErrors['dataError'][] = array(
                "name" => $controller.".tax_code", 
                "status" => 'TAXCODEEXIST',
                'returnData' => $returnData
            );
        }
        return $fieldErrors;
    }

    public static function processUserCRUD($controller, $action, $email, $country_pcode, $phone, $id, $chain_code, $permission){
        $sql_model = new VanillaModel();
        $condition = '';
        $errors = array();
        if(!empty($email) || (!empty($phone) && !empty($country_pcode))){
            $phone = (!empty($phone) && !empty($country_pcode)) ?  $country_pcode.$phone : '';
            if($action === 'create'){
                if($controller == 'customers'){
                    $condition = 'customer_id';
                    $new_password = substr(str_shuffle(str_repeat("0123456789", 6)), 0, 6);
                    ## $users = $sql_model->queryWithStatus('INSERT INTO users (email, phone, password, chain_code, '.$condition.', app_permission_customer) VALUES ("' . $email . '", "' . $phone . '", "' . hash('sha256', $new_password) . '", "'.$chain_code.'", "'.$id.'", "'.$permission.'")');
                    $users = $sql_model->queryWithStatus("
                        INSERT INTO users (email, phone, password, chain_code, ".$condition.", app_permission_customer) 
                        VALUES ('" . $email . "', '" . $phone . "', '" . hash("sha256", $new_password) . "', '".$chain_code."', '".$id."', '".$permission."')
                    ");
                    if($users['status'] == "OK"){
                        if(!empty($phone)){
                            $sms = Sms::send_sms_for_password($phone, $new_password, $chain_code);
                            if($sms['status'] == 'ERROR' && !empty($email)){
                                Email::send_mail_for_password($email, $new_password);
                            };
                        }else if(empty($phone) && !empty($email)){
                            Email::send_mail_for_password($email, $new_password);
                        }
                    }
                }
            }else if($action === 'edit'){
                if($controller == 'customers'){
                    $condition = 'WHERE customer_id='.$id;
                }else if($controller == 'employees'){
                    $condition = 'WHERE employee_id='.$id;
                }
                $user = $sql_model->queryWithOneResultSet('SELECT * FROM users '.$condition.''); 
                if($user){
                    $users =  $sql_model->queryWithStatus('UPDATE users SET email= "'.$email.'", phone= "'.$phone.'"'.$condition);
                }else{
                    if($controller == 'customers'){
                        $condition = 'customer_id';
                        $new_password = substr(str_shuffle(str_repeat("0123456789", 6)), 0, 6);
                        $users = $sql_model->queryWithStatus('INSERT INTO users (email, phone, password, chain_code, '.$condition.') VALUES ("' . $email . '", "' . $phone . '", "' . hash('sha256', $new_password) . '", "'.$chain_code.'", "'.$id.'")');
                        if($users['status'] == "OK"){
                            if(!empty($phone)){
                                $sms = Sms::send_sms_for_password($phone, $new_password, $chain_code);
                                if($sms['status'] == 'ERROR' && !empty($email)){
                                    Email::send_mail_for_password($email, $new_password);
                                };
                            }else if(empty($phone) && !empty($email)){
                                Email::send_mail_for_password($email, $new_password);
                            }
                        }
                    }                    
                }
            }else if($action === 'remove'){
                if($controller == 'customers'){
                    $condition = 'WHERE customer_id='.$id;
                }else if($controller == 'employees'){
                    $condition = 'WHERE employee_id='.$id;
                }
                $user = $sql_model->queryWithOneResultSet('SELECT * FROM users '.$condition); 
                if($controller == 'customers'){
                    if($user['employee_id'] != 0){
                        $condition = 'SET customer_id = 0 WHERE id='.$user['id'];
                        $users = $sql_model->queryWithStatus('UPDATE users '.$condition);
                    }else{
                        $users = $sql_model->queryWithStatus('DELETE FROM users WHERE id='.$user['id']);  
                    }
                }else if($controller == 'employees'){
                    if($user['customer_id'] != 0){
                        $condition = 'SET customer_id = 0 WHERE id='.$user['id'];
                        $users = $sql_model->queryWithStatus('UPDATE users '.$condition);
                    }else{
                        $users = $sql_model->queryWithStatus('DELETE FROM users WHERE id='.$user['id']);  
                    }
                }
            }
        }
        return $errors;
    }
    function __destruct() {
    }
}
