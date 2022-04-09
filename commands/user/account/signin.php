<?php
class signin {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getDataRunApp':
                $this->getDataRunApp();
                break;
            case 'admin_signin':
                $this->admin_signin();
                break;
            case 'admin_logout':
                $this->admin_logout();
                break;
            case 'client_signin':
                $this->client_signin();
                break;
            case 'getCountry':
                $this->getCountry();
                break;
            case 'getLanguageList':
                $this->getLanguageList();
                break;

            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action');
                _json_echo('signin', $response);
        }
    }
    
    function getDataRunApp(){
        $mod = 'global';
        $route = 'chain';
        $controller = 'chainGlobal';
        $action = 'getChainByHostName';
        Utility::callLocalFunction($mod, $route, $controller, $action);
    }

    function admin_signin() {
        $response = array('status' => 'ERROR', 'message' => 'none', 'data'=>array());
        $data = Utility::processedData(true);
        $username = $data['username'];
        $password = hash('sha256', $data['password']);
        $fcm_token = isset($data['fcm_token']) ? $data['fcm_token'] : '';
        $today = date('Y-m-d H:i:s');
        $user = $this->sql_model()->queryWithOneResultSet('
            SELECT id FROM users 
            WHERE (email = "'. $username . '" OR phone = "'. $username . '") 
            AND password = "'. $password .'"');
        if (!$user) {
            $response['status'] = 'ERROR';
        } else {
            $session_id = session_id();
            $response['data']['base64'] = base64_encode($user['id'] . '|' . time() . '|' . $session_id);
            $response['status'] = 'OK';
            $_SESSION['user'] = $user;
            ### Update token 
            if($fcm_token){
                $skip = array();
                $json = array();
                $fcm_data = array(
                    'id' => $user['id'],
                    'fcm_token' => $fcm_token
                );
                $fcm_result = Utility::processedSaveData('users', $fcm_data, $skip, $json);
            }
        }
        _json_echo('admin_login', $response);
    }
    function admin_logout() {
        $response = array('status' => 'ERROR', 'message' => 'none', 'data'=>array());
        $data = Utility::processedData(true);
        $fcm_token = isset($data['fcm_token']) ? $data['fcm_token'] : '';
        ### Update token 
        if($fcm_token){
            $personal_id = ($data['action_code'] == 'ESCP') ? 'customer_id' : 'employee_id';
            $user = $this->sql_model()->queryWithOneResultSet('
                SELECT id FROM users 
                WHERE ('. $personal_id . ' = "'. $data['id'] . '" AND fcm_token = "'. $fcm_token . '") 
            ');
            if($user['id']){
                $skip = array();
                $json = array();
                $fcm_data = array(
                    'id' => $user['id'],
                    'fcm_token' => ''
                );
                $fcm_result = Utility::processedSaveData('users', $fcm_data, $skip, $json);
            }

        }
        $response['status'] = 'OK';
        _json_echo('admin_logout', $response);
    }
    function getCountry(){
        $mod = 'global';
        $route = 'country';
        $controller = 'countryGlobal';
        $action = 'getCountry';
        Utility::callLocalFunction($mod, $route, $controller, $action);
    }

    function getLanguageList(){
        $mod = 'global';
        $route = 'languages';
        $controller = 'languagesGlobal';
        $action = 'getLanguageList';
        Utility::callLocalFunction($mod, $route, $controller, $action);
    }
  
    function __destruct() {
    }
}
