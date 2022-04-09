<?php

class User {

    public $Client;
    function __construct() {
        $this->Client = new ClientModel();
    }
	function beforeAction ($action) {
        // logErrorDebug('action: ' .$action);
        switch ($action) {
            case 'signin':
                $this->signin();
                break;
            case 'signup':
                $this->signup();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Error');
                _json_echo('signin', $response);
        }
    }
    function signin(){
        $response = array('status' => 'ERROR', 'message' => 'signin');
        $data = Utility::processedData();
        $username = $data['username'];
        $password = hash('sha256', $data['password']);
        // $token = $data['token']);
        if(is_numeric($username)){
            $sql = 'SELECT * FROM users WHERE phone="'. $username .'" AND password='. $password;
        }else{
            $sql = 'SELECT * FROM users WHERE username="'. $username .'" AND password="'. $password .'"';
        }
        $signin =  $this->Client->queryWithOneResultSet($sql);
        if($signin){
            $response['status'] = 'OK';
            if($signin['customer_id'] != 0){
                if(!empty($signin['app_role_customer'])){
                    $response['role'] = array('customer'=>null);
                    $roleArr = explode("|", $signin['app_role_customer']);
                    foreach($roleArr as $r){    
                        $role[$r] = true;
                    }
                    $response['role']['customer'] = $role;
                }
            }
            if($signin['employee_id'] != 0){
                if(!empty($signin['app_role_employee'])){
                    $roleArr = explode("|", $signin['app_role_employee']);
                    foreach($roleArr as $r){    
                        $role[$r] = true;
                    }
                    $response['role']['employee'] = $role;
                }
            }
        }else{
            $response = array(
                'status' => 'ERROR', 
                'message' => 'Tài khoản hoặc mật khẩu không đúng!'
            );
        }
        _json_echo('signin', $response);
    }
    function signup(){
        $response = array('status' => 'ERROR', 'message' => 'signup');
        // logErrorDebug('signup - POST: ' . _json_encode($_POST));
        // logErrorDebug('signup - POST');
        _json_echo('signup', $response);
    }

    function checkUsername($username){
        
    }

    function __destruct() {
    }

}
