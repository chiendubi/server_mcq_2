<?php

header('Access-Control-Allow-Origin: *');

class OldUserController extends VanillaController {
	public $OldUser;
    function __construct() {
        $this->OldUser = new OldUser();
    }
	function beforeAction () {

	}
    function admin_getPath() {
         $response = array('status' => 'ERROR', 'message' => 'admin_getPath');
         _json_echo('admin_getPath', $response);
    }
    function admin_getInfoUser() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        // get value POST
        $user_id = $_POST['user_id'];
        // get info user
        $user = $this->OldUser->queryWithOneResultSet('SELECT * FROM users WHERE id = ' . $user_id);
        if ($user) {
            $response['status'] = 'OK';
            $response['info'] = $user;
        } else {
            $response['message'] = 'System error. Please login again.';
        }
        _json_echo('admin_getInfoUser', $response);
    }
    // log in user
    // Input: email, password
    function admin_login() {

        $data = $this->OldUser->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        $data = json_decode($data, true);

        logError(print_r($data['username'], true));
        // set basic data
        $this->render = false;  // to return json to mobile client
        $response = array('status' => 'ERROR', 'message' => 'none');
        $username = $data['username'];
        $password = $data['password'];
        $password = hash('sha256', $password);
        $today = date('Y-m-d H:i:s');
        $user = $this->OldUser->queryWithOneResultSet('
            SELECT id FROM users 
            WHERE (email = "'. $username . '" OR phone = "'. $username . '") 
            AND password = "'. $password .'"');
        if ($user == null) {
            $response['message'] = 'Email hoặc mật khẩu không đúng!';
        } else {
            $session_id = session_id();
            $user['base64'] = base64_encode($user['id'] . '|' . time() . '|' . $session_id);
            $response['status'] = 'OK';
            $response['message'] = "Đăng nhập thành công!";
            $response['info'] = $user;

            // add to onlineuser
            $user_id = $user['id'];

            // sometime user_id is left in system
            $onlineuser = $this->OldUser->queryWithOneResultSet('SELECT time_stamp FROM onlineusers WHERE user_id = ' . $user_id);
            // always delete old session

            if ($onlineuser)
                $this->OldUser->queryWithStatus("DELETE FROM onlineusers WHERE user_id=" . $user_id);
            // and create a new one
            $sql = 'INSERT INTO onlineusers(user_id, session_id, time_stamp) VALUES('. $user_id .', "'. $session_id .'", "' . $today . '")';
            $result = $this->OldUser->queryWithStatus($sql);
            // Store user data into global session data
            $_SESSION['user'] = $user;
        }
        _json_echo('admin_login', $response);
        // die();
    }
    // get user data
    // Input: user_id
    function admin_getUserName() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        $id = $this->OldUser->real_escape_string($_POST['user_id']);
        $ss_id = $this->OldUser->real_escape_string($_POST['ss_id']);
        // check online user
        $session_id = session_id();
        if (DEVELOPMENT_ENVIRONMENT) logError("SESSION_ID - " . $session_id);
        $onlineUser = $this->OldUser->queryWithOneResultSet('SELECT * FROM onlineusers WHERE user_id = ' . $id . ' AND session_id = "' . $ss_id . '"');
        if ($onlineUser == false) {
            $response['message'] = "Your session has expired, please login again.";
            _json_echo('admin_getUserName', $response);
            return;
        }
        $user = $this->OldUser->queryWithOneResultSet('SELECT * FROM users WHERE id = ' . $id);
        $customer_id = $user['customer_id'];
        $employee_id = $user['employee_id'];
        $chain_code = $user['chain_code'];
        $user_information = array();
        $chainPermission = connect_core::sqlQuery('SELECT website_permission FROM chain WHERE code ="'.$chain_code.'"');
        if ($user['customer_id'] != 0){
            $permission_arr = array();
            $permission = explode('|', $user['website_permission_customer']);
            if(count($permission) > 0){
                if(count($chainPermission['info']['rows']) > 0){
                    $modules = explode('|', $chainPermission['info']['rows'][0]['website_permission']);
                    foreach($permission as $per){
                        if(array_search($per, $modules) > -1){
                            $permission_arr[$per] = true;
                        }
                    }
                }
            }
            $customer = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE id = ' . $customer_id);
            $user_information[] = array(
                'action_code' => 'ESCP',
                'id' => $customer['id'],
                'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                'position' => USER_PATIENT_NAME,
                'clinic' => $customer['clinic'],
                'permission' => $permission_arr,
                'chain_code' => $chain_code,
            );
        }
        if($user['employee_id'] != 0){
            $permission_arr = array();
            $permission = explode('|', $user['website_permission_employee']);
            if(count($permission) > 0){
                if(count($chainPermission['info']['rows']) > 0){
                    $modules = explode('|', $chainPermission['info']['rows'][0]['website_permission']);
                    foreach($permission as $per){
                        if(array_search($per, $modules) > -1){
                            $permission_arr[$per] = true;
                        }
                    }
                }
            }
            $employee = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE id = ' . $employee_id);
            $user_information[] = array(
                'action_code' => 'ESEP',
                'id' => $employee['id'],
                'name' => $employee['last_name'] . ' ' . $employee['first_name'],
                'position' => $employee['position'],
                'clinic' => $employee['clinic'],
                'permission' => $permission_arr,
                'chain_code' => $chain_code
            );
        }

        $clinics = $this->OldUser->queryWithResultSet('SELECT * FROM clinics');
        if(count($clinics['info']['rows']) > 1){
            $response['clinics'] = true;
        }else{
            $response['clinics'] = false;
        }
        $response['status'] = 'OK';
        $response['message'] = "Log in successful!";
        $response['info'] = $user_information;
        _json_echo('admin_getUserName', $response);
    }
    function admin_deleteSession() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');    
        $id = $this->OldUser->real_escape_string($_POST['user_id']);
        $onlineUser = $this->OldUser->queryWithStatus('DELETE FROM onlineusers WHERE user_id = ' . $id);
        _json_echo('admin_deleteSession', $response);
    }
    function admin_openLockScreen(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $username = $this->OldUser->real_escape_string($_POST['username']);
        $password = $this->OldUser->real_escape_string($_POST['password']);
        $password = hash('sha256', $password);
        $user = $this->OldUser->queryWithOneResultSet('SELECT id FROM users WHERE username = "'. $username . '" AND password = "'. $password .'"');
        if ($user) {
            $response['status'] = 'OK';
        }
        _json_echo('admin_openLockScreen', $response);
    }
	function admin_getUser() {
        ob_start();
        require_once(SERVER_ROOT . '/editor/app/users.php');
        $data = ob_get_contents();
        ob_end_clean();
        $adata = json_decode($data,true);
        _json_echo('admin_getUser', $adata);
    }
    function admin_setUser() {
        $response = array('status' => 'ERROR', 'message' => '');
        $post = _json_encode($_POST);
        $apost = json_decode($post,true);
        // get first key and value, and other data
        $array = $apost['data'];
        $first_key = key($array); 
        $first_value = reset($array);
        $user =  $first_value['users'];
        $email = $this->OldUser->real_escape_string($user['email']);
        $phone = $this->OldUser->real_escape_string($user['phone']);
        $email = strtolower(trim($email));
        $password = $user['password'];
        $password = hash('sha256', $password);
        $error = false;
        $fieldErrors = array();
        $data = array();
        $action = $apost['action'];
        if ($action === 'create') {
            // check user already existing
            $user_existing = $this->OldUser->queryWithOneResultSet('SELECT * FROM users WHERE email = "' . $email . '" OR phone = "' . $phone . '" ');
            if ($user_existing) {
                if($user_existing['email']){
                    $fieldErrors[] = array("name" => "users.email", "status" => "`" . $email . "` đã được sử dụng. Vui lòng kiểm tra lại.");
                }else{
                    $fieldErrors[] = array("name" => "users.phone", "status" => "`" . $phone . "` đã được sử dụng. Vui lòng kiểm tra lại.");
                }
                $error = true;    
            } 
            if ($error) {
                $response = array('status' => 'ERROR', 'message' => 'Error in admin_setUser');
                $response['data'] = $data;
                $response['fieldErrors'] = $fieldErrors;
                _json_echo('admin_setUser', $response);
                return;
            } 
        }
        require_once(SERVER_ROOT . '/editor/app/users.php');
    }
    function admin_getCustomers() {
        $page= $_GET['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $customers = $this->OldUser->queryWithResultSet('
            SELECT id, code, last_name, first_name, email, country_pcode1, phone1 FROM customers
            WHERE 
            first_name LIKE "'.$_GET['term'].'%" LIMIT '.$end.','.$start.'
        ');
        // NOT EXISTS
        // (
        //    SELECT email, phone FROM users
        //    WHERE users.email = customers.email OR users.phone =  concat(customers.country_pcode1, customers.phone1) 
        // ) AND
        logError('debug : ' .print_r($customers, true));
        $count = count($customers['info']['rows']);
        $data = array('id'=>'', 'name'=>'', 'total_count'=>'');
        if($count > 0){
            foreach($customers['info']['rows'] as $cs){
                $data[] = array(
                    'id'=>$cs['id'].'|'.$cs['email'].'|'.$cs['country_pcode1'].$cs['phone1'],
                    'name'=>$cs['last_name'] . ' ' . $cs['first_name'] . ' (' . $cs['code'] . ')', 'total_count'=>$count ); 
            }
        }
        _json_echo('admin_getCustomers', $data);
    }
    function admin_getEmployees() {
        $data = Utility::processedData();
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $customers = $this->OldUser->queryWithResultSet('
            SELECT id, code, last_name, first_name, email, country_pcode1, phone1 FROM employees
            WHERE 
            NOT EXISTS
			(
                SELECT email, phone FROM users
                WHERE users.email = employees.email OR users.phone =  concat(employees.country_pcode1, employees.phone1) 
            ) 
            AND first_name LIKE "%'.$data['term'].'%" LIMIT '.$end.','.$start.'
        ');
        $count = count($customers['info']['rows']);
        $data = array('id'=>'', 'name'=>'', 'total_count'=>'');
        if($count > 0){
            foreach($customers['info']['rows'] as $cs){
                $data[] = array(
                    'id'=>$cs['id'].'|'.$cs['email'].'|'.$cs['country_pcode1'].$cs['phone1'],
                    'name'=>$cs['last_name'] . ' ' . $cs['first_name'] . ' (' . $cs['code'] . ')', 'total_count'=>$count ); 
            }
        }
        _json_echo('admin_getCustomers', $data);
    }
    function admin_checkPermissions() {
        $response = array('status' => 'ERROR', 'message' => '');

        $user_id = $_POST['user_id'];
        $code_action = $_POST['code_action'];
        // check
        $permission = $this->OldUser->queryWithOneResultSet('SELECT permission FROM role_relationships WHERE role_group_id = (SELECT role_group_id FROM users WHERE id = '.$user_id.') AND role_detail_id = (SELECT id FROM role_details WHERE code_action = "'.$code_action.'")');
        if ($permission) {
            $permission = $permission['permission'];

            $response['status'] = 'OK';
            $response['message'] = "Permission allow!";
            $response['info'] = $permission;
        } else {
            $response['message'] = "Server Error!";
        }
        
        _json_echo('admin_getUserName', $response);
    }
    function admin_getRoleGroups() {
        $response = array('status' => 'ERROR', 'message' => '');

        $role_groups = $this->OldUser->queryWithResultSet('SELECT * FROM role_groups');
        if ($role_groups['status'] == "OK") {
            $role_groups = $role_groups['info']['rows'];

            $response['status'] = 'OK';
            $response['info'] = $role_groups;
        } else {
            $response['message'] = "Server Error!";
        }
        
        _json_echo('admin_getRoleGroups', $response);
    }
    function admin_getRoleRelationships() {
        $response = array('status' => 'ERROR', 'message' => '');
        $isOk = true;

        $role_group_id = $_POST['role_group_id'];

        // get modules Lv.1
        $modules = $this->OldUser->queryWithResultSet('SELECT rr.*, rd.id AS module_id, rd.name_action, rd.parent_id, rd.code_action, rd.description FROM role_relationships rr JOIN role_details rd ON rr.role_detail_id=rd.id WHERE rr.role_group_id=' . $role_group_id . ' AND parent_id=0');
        // error_log(print_r($modules, true));
        if ($modules['status'] == "OK") {
            $modules = $modules['info']['rows'];
            for ($i=0; $i < count($modules); $i++) { 
                $module_id = $modules[$i]['module_id'];
                $modules[$i]['sub_modules'] = array();

                // get sub modules Lv.2
                $sub_modules = $this->OldUser->queryWithResultSet('SELECT rr.*, rd.id AS module_id, rd.name_action, rd.parent_id, rd.code_action, rd.description FROM role_relationships rr JOIN role_details rd ON rr.role_detail_id=rd.id WHERE rr.role_group_id=' . $role_group_id . ' AND rd.parent_id=' . $module_id);
                // error_log(print_r($sub_modules, true));
                if ($sub_modules['status'] == "OK") {
                    $sub_modules = $sub_modules['info']['rows'];
                    // include sub modules
                    for ($j=0; $j < count($sub_modules); $j++) { 
                        array_push($modules[$i]['sub_modules'], $sub_modules[$j]);
                        
                        $sub_module_id = $sub_modules[$j]['module_id'];
                        $modules[$i]['sub_modules'][$j]['actions'] = array();
                        
                        // get action Lv.3
                        $actions = $this->OldUser->queryWithResultSet('SELECT rr.*, rd.id AS module_id, rd.name_action, rd.parent_id, rd.code_action, rd.description FROM role_relationships rr JOIN role_details rd ON rr.role_detail_id=rd.id WHERE rr.role_group_id=' . $role_group_id . ' AND rd.parent_id=' . $sub_module_id);
                        // error_log(print_r($actions, true));
                        if ($actions['status'] == "OK") {
                            $actions = $actions['info']['rows'];
                            // include actions
                            for ($k=0; $k < count($actions); $k++) { 
                                array_push($modules[$i]['sub_modules'][$j]['actions'], $actions[$k]);
                            }
                        } else {
                            $isOk = false;
                            break;
                        }

                    }
                } else {
                    $isOk = false;
                    break;
                }

            }
        } else {
            $isOk = false;
        }

        if ($isOk) {
            $response['status'] = "OK";
            $response['info'] = $modules;
        } else {
            $response['message'] = "Server Error!";
        }
        
        _json_echo('admin_getRoleRelationships', $response);
    }
    function admin_savePermissions() {
        $response = array('status' => 'ERROR', 'message' => '');
        $isOk = true;

        $role_group_id = $_POST['role_group_id'];
        $permission_ids = $_POST['permission_ids'];
        // $permission_ids_str = implode(', ', $permission_ids);

        $sql1 = 'UPDATE role_relationships SET permission = 1 WHERE id IN ('. $permission_ids. ')';
        error_log($sql1);
        $result1 = $this->OldUser->queryWithStatus($sql1);

        $sql2 = 'UPDATE role_relationships SET permission = 0 WHERE role_group_id = ' . $role_group_id . ' AND id NOT IN ('. $permission_ids. ')';
        error_log($sql2);
        $result2 = $this->OldUser->queryWithStatus($sql2);

        if ($result1['status'] == "OK") {
            $response['status'] = "OK";
            $response['message'] = "Success";
        } else {
            $response['message'] = "Server Error!";
        }
        
        _json_echo('admin_savePermissions', $response);
    }
    function client_fbSignin() {
        
        logErrorDebug('client_fbSignin - POST: ' . _json_encode($_POST));

        $data = $this->OldUser->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        $data = json_decode($data, true);

        $email = $data['email'];
        $name = $data['name'];
        $fbid = $data['fbid'];
        $picture = $data['picture'];
        $fcm_token = $data['token'];
        // $phone = $data['phone'];

        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');        

        // Check if fbuser exist
        $fbuser = $this->OldUser->queryWithOneResultSet('SELECT * FROM fbusers WHERE email = "' . $email . '"');

        if ($fbuser == null) {
            // create new fbuser
            $notification = NOTIFICATION_MESSAGE;
            $latitude = 0.0;
            $longitude = 0.0;
            $result = $this->OldUser->queryWithStatus('INSERT INTO fbusers (signin_date, email, name, fbid, picture, fcm_token, latitude, longitude) VALUES (NOW(), "' . $email . '",  "' . $name . '","' . $fbid . '","' . $picture . '","' . $fcm_token . '",' . $latitude. ',' . $longitude . ')');
            $id = $result['info']['id'];

        } else {
            // update possibly-changed data: fbid, fcm_token
            $checkToken = $this->OldUser->queryWithResultSet('SELECT * FROM fbusers WHERE fcm_token = "' . $fcm_token . '"');
            // logErrorDebug('Check Token - response: ' . print_r($checkToken, true));   
            if(count($checkToken['info']['rows']) > 0){
                for ($i = 0 ; $i < count($checkToken['info']['rows']); $i++) {
                    $id =  $checkToken['info']['rows'][$i]['id'];
                    $nullToken = '';
                    $updateToken = $this->OldUser->queryWithStatus('UPDATE fbusers SET fcm_token = "' . $nullToken . '"  WHERE id = "' . $id . '"');
                }
            }
            $result = $this->OldUser->queryWithStatus("UPDATE fbusers SET fbid='" . $fbid . "', name='" . $name . "', fcm_token='" . $fcm_token . "', picture ='" . $picture . "' WHERE id= '" . $fbuser['id'] ."'");
            $id = $fbuser['id'];
        }

        // now get user info for client
        $user = array('id' => $id, 'type' => array(), 'code' => '', 'short_name' => '', 'employee_id' => 0, 'customer_id' => 0);       
        array_push($user['type'],array('value' =>USER_GUEST , 'name'=>USER_GUEST_NAME));
        // check user email
        $employee = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE email = "'. $email . '"');
        if ($employee == null) {
            // get user from customers
            $customer = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE email = "'. $email . '"');
            if ($customer == null) {
                // guest login
            } else {
                // Get customer_code
                $width = 5;
                // $padded = str_pad((string)$customer['id'], $width, "0", STR_PAD_LEFT); 
                // $customer_code = $customer['clinic'] . '_' . $padded .'_'. CUSTOMER_TYPE;  
                $customer_code = $customer['clinic_code']; 
               // get customer data
                // $user['type'] = USER_PATIENT;
                $user['code'] = $customer_code;
                $user['customer_id'] = $customer['id'];
                $user['clinic'] =  $customer['clinic'];
                $user['type'] = array();
                array_push($user['type'],array('value' =>USER_PATIENT , 'name'=>USER_PATIENT_NAME));
            }

        } else {
            // get employee data
            $user['type'] = array();
            $positions = explode('|', $employee['position']);
            foreach ($positions as $p) {
                if ($p == ES_ADMINISTRATOR) 
                    array_push($user['type'],array('value' =>USER_ADMINISTRATOR , 'name'=>ES_ADMINISTRATOR));
                else if ($p == ES_MANAGER) 
                    array_push($user['type'],array('value' =>USER_MANAGER , 'name'=>ES_MANAGER));
                else if ($p == ES_EREAL_MANAGER) 
                    array_push($user['type'],array('value' =>USER_EREAL_MANAGER , 'name'=>ES_MANAGER));
                else if ($p == ES_CEO) 
                    array_push($user['type'], array('value' =>USER_CEO , 'name'=> $p));
                else if($p == ES_DOCTOR)
                    array_push($user['type'], array('value' =>USER_DOCTOR , 'name'=> $p));
                else if($p == ES_CONSULTING_DOCTOR)
                    array_push($user['type'], array('value' =>USER_CONSULTING_DOCTOR , 'name'=> $p));
                else if( $p == ES_DOCTOR_IN_CHARGE_OF_EXPERTISE )
                    array_push($user['type'], array('value' =>USER_DOCTOR_IN_CHARGE_OF_EXPERTISE , 'name'=> $p));
                else if( $p == ES_EXPERT_DOCTOR )
                    array_push($user['type'], array('value' =>USER_EXPERT_DOCTOR , 'name'=> $p));
                else if( $p == ES_EREAL_DOCTOR_IN_CHARGE_OF_EXPERTISE )
                    array_push($user['type'], array('value' =>USER_EREAL_DOCTOR_IN_CHARGE_OF_EXPERTISE , 'name'=> $p));
                else if ($p == ES_EREAL_EXPERT_DOCTOR) 
                    array_push($user['type'], array('value' =>USER_EREAL_EXPERT_DOCTOR , 'name'=> $p));
                else if ($p == ES_ASSISTANT) 
                    array_push($user['type'],array('value' =>USER_ASSISTANT , 'name'=>ES_ASSISTANT));
                else if ($p == ES_RECEPTIONIST)
                    array_push($user['type'],array('value' =>USER_RECEPTIONIST, 'name'=>ES_RECEPTIONIST));
                else if ($p == ES_EREAL_HEAD_NURSE)
                    array_push($user['type'],array('value' =>USER_EREAL_HEAD_NURSE, 'name'=>ES_EREAL_HEAD_NURSE));
                else if ($p == USER_VICE_PRESIDENT_NAME)
                    array_push($user['type'],array('value' =>USER_VICE_PRESIDENT, 'name'=>USER_VICE_PRESIDENT_NAME));
                else if ($p == ES_EREAL_MAJOR_ACCOUNTING)
                    array_push($user['type'],array('value' =>USER_EREAL_MAJOR_ACCOUNTING, 'name'=>ES_EREAL_MAJOR_ACCOUNTING));
                else if ($p == ES_HEAD_NURSE)
                    array_push($user['type'],array('value' =>USER_HEAD_NURSE, 'name'=>ES_HEAD_NURSE));
                else if ($p == USER_SALES_MANAGER_NAME)
                    array_push($user['type'],array('value' =>USER_SALES_MANAGER, 'name'=>USER_SALES_MANAGER_NAME));
                else if ($p == ES_CUSTOMER_CARE)
                    array_push($user['type'],array('value' =>USER_CUSTOMER_CARE, 'name'=>ES_CUSTOMER_CARE));
                else if ($p == ES_MAKERTING)
                    array_push($user['type'],array('value' =>USER_MAKERTING, 'name'=>ES_MAKERTING));
                else if ($p == ES_MAJOR_ACCOUNTING)
                    array_push($user['type'],array('value' =>USER_MAJOR_ACCOUNTING, 'name'=>ES_MAJOR_ACCOUNTING));
                else if ($p == ES_ACCOUNTING)
                    array_push($user['type'],array('value' =>USER_ACCOUNTING, 'name'=>ES_ACCOUNTING));
                else if ($p == ES_GENERAL_STAFF)
                    array_push($user['type'],array('value' =>USER_GENERAL_STAFF, 'name'=>ES_GENERAL_STAFF));
                else if ($p == ES_COLLABORATOR)
                    array_push($user['type'],array('value' =>USER_COLLABORATOR, 'name'=>ES_COLLABORATOR));
                else if ($p == ES_INSURANCE)
                    array_push($user['type'],array('value' =>USER_INSURANCE, 'name'=>ES_INSURANCE));
                else if ($p == ES_LABO)
                    array_push($user['type'],array('value' =>USER_LABO, 'name'=>USER_LABOT_NAME));
                else if ($p == ES_SUPPLY_SYSTEM)
                    array_push($user['type'],array('value' =>USER_SUPPLY, 'name'=>ES_SUPPLY_SYSTEM));
                   
            }          
            $user['short_name'] = $employee['short_name'];
            $user['employee_id'] = $employee['id'];
        }

        $response['status'] = 'OK';
        $response['message'] = "Đăng nhập thành công!";
        $response['user'] = $user;            

        // add to onlineuser
        // $this->setOnlineusers($user);
        _json_echo('client_fbSignin', $response);
        //logErrorDebug('client_fbSignin - response: ' . print_r($response, true));   

    }
    function client_getDataSet() {

        logErrorDebug('client_getDataSet - POST: ' . _json_encode($_POST));

        // get system info
        $systems = $this->OldUser->queryWithResultSet('SELECT data_set, test_mode FROM systems WHERE id=1');
        $system = $systems['info']['rows'][0];
        if ($system['test_mode'] != 1) {
            // real-time data, use it
            $dataset = array('id' => $system['test_mode']);

        } else {

            // get fbusers info
            $fbusers = $this->OldUser->queryWithResultSet('SELECT * FROM fbusers');

            $users_guest = array();
            $users_customer = array();
            $users_doctor = array();
            $users_assistant = array();
            $users_receptionist = array();
            $users_manager = array();
            $users_admin = array();

            // categorize data by role
            $fbusers = $fbusers['info']['rows'];
            for ($ie = 0 ; $ie < count($fbusers); $ie++) {
                $fbuser = $fbusers[$ie];
                $email = $fbuser['email'];
                // check user email
                $employee = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE email = "'. $email . '"');
                if ($employee == null) {
                    // not an employee, try customer
                    $customer = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE email = "'. $email . '"');
                    if ($customer == null) {
                        // not a customer, must be a guest
                        array_push($users_guest, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Khách)';
                    } else {
                        array_push($users_customer, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Bệnh nhân)';
                    }
                } else {
                    if ($employee['position'] == ES_ADMINISTRATOR) {
                        array_push($users_admin, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Quản trị)';
                    }
                    else if ($employee['position'] == ES_MANAGER) {
                        array_push($users_manager, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (ES_MANAGER)';
                    }
                    else if ($employee['position'] == ES_DOCTOR) {
                        array_push($users_doctor, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Bác sĩ)';
                    }
                    else if ($employee['position'] == ES_ASSISTANT) {
                        array_push($users_assistant, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Trợ thủ)';
                    }
                    else if ($employee['position'] == ES_RECEPTIONIST || $employee['position'] == ES_CASHIER) {
                        array_push($users_receptionist, $fbuser);
                        $fbusers[$ie]['display_name'] = $fbusers[$ie]['name'] . ' (Lễ tân)';
                    }
                }
            }

            $dataset = array('id' => $system['test_mode'], 'fbusers' => $fbusers);
        }

        // $dataset = array('id' => $system['data_set'], 
        //     'fbusers' => array(
        //     'guest' => $users_guest, 'customer' => $users_customer, 'doctor' => $users_doctor, 
        //     'assistant' => $users_assistant, 'receptionist' => $users_receptionist,
        //     'manager' => $users_manager, 'admin' => $users_admin),
        //     'doctors' => $users_doctor); 

        $response = array('status' => 'OK', 'message' => 'none', 'dataset' => $dataset);

        _json_echo('client_getDataSet', $response);
         logErrorDebug('client_getDataSet - response: ' . print_r($response, true));   
    }
    function client_getNotification() {
        logErrorDebug('client_getNotification - POST: ' . _json_encode($_POST));
        // get email from device token
        $response = array('status' => 'ERROR', 'message' => 'none');  
        $fcm_token = $this->OldUser->real_escape_string($_POST['token']);

        $fbuser = $this->OldUser->queryWithOneResultSet('SELECT email FROM fbusers WHERE fcm_token = "' . $fcm_token . '"');
        if ($fbuser) {
            $email = $fbuser['email'];
            $messages = $this->OldUser->queryWithResultSet('SELECT * FROM messages WHERE email = "' . $email . '" ORDER BY cdate DESC');
            if (count($messages['info']['rows']) > 0) {
                $message = $messages['info']['rows'][0];
                // get the latest
                $response['id'] = $message['id'];
                $response['title'] = $message['title'];
                $response['message'] = $message['message'];
                $response['status'] = 'OK';
            }
        } else {
            $response['title'] = 'LỖI';
            $response['message'] = 'Không đọc được tin nhắn';
        }

        _json_echo('client_getNotification', $response);
        // logErrorDebug('client_getNotification - response: ' . print_r($response, true));   
    }
    function sendNotification($email, $title,  $message) {
        $result = null;
        $fbuser = $this->OldUser->queryWithOneResultSet('SELECT fcm_token FROM fbusers WHERE email = "' . $email . '"');
        if ($fbuser && $fbuser['fcm_token'] != '') {
            $device_token = $fbuser['fcm_token'];

            if (LOCAL_DATA)
                $result = array('status' => 'OK', 'message' => 'Local mode');        
            else {
                $push_result = $this->sendPushFCM($device_token, $title, $message);
                if ($push_result)
                    $result = array('status' => 'OK', 'message' => 'Remote mode');        
            }

            if ($result) {
                // add a new message
                $res = $this->OldUser->queryWithStatus('INSERT INTO messages (cdate,email,title, message) VALUES (NOW(), "' . $email . '","' . $title . '","'. $message .'")');
                if ($res)
                    $result['message_id'] = $res['info']['id'];
                else
                    $result['message_id'] = 0;
            }
        }
        return $result;
    }
    private function sendPushFCM($token, $title, $message) {

        // $token ='ctUYPKopQWY:APA91bF_uZKoShoaZEzMdi3GBHafHotLfENAVhrJrq81myS9gbD28HIf26_Vv4vY8xod89i6AZ2mrnYZWOb-XiRuxzTCHi0k9uuceOGQLmzpcmus06eT97MDsO1mZceo3SpnA1aQf1-vexgDOR5zBNf39o8DYuBcIg';
        // logError('sendPushFCM() - $token = ' . $token);

        // #prep the bundle     
        $msg = array(
            'title' => $title,
            'body'  => $message,
            'badge' => 1,
            'icon'  => 'myicon',    /*Default Icon*/
            'sound' => 'mySound',   /*Default sound*/
            'click_action' => 'FCM_PLUGIN_ACTIVITY',
            // 'content-available' => 0,
            // 'style' => 'inbox',
            // 'summaryText' => 'There are %n% notifications',
            // 'notId': 1
        );

        // Validate FCM registration ID
        $headers = array(
            'Authorization: key=' . FCM_SERVER_KEY,
            'Content-Type: application/json'
        );

        logErrorDebug('sendPushFCM() - $token = ' . print_r($token, true));
        logErrorDebug('sendPushFCM() - $msg = ' . print_r($msg, true));
        logErrorDebug('sendPushFCM() - $headers = ' . print_r($headers, true));

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://iid.googleapis.com/iid/info/' . $token . '?details=true' );
        curl_setopt( $ch,CURLOPT_POST, false );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        $result1 = curl_exec($ch );
        $result2 = json_decode($result1, true);

        logErrorDebug('sendPushFCM() - $result2 = ' . print_r($result2, true));

        // Check platform
        $platform = $result2['platform'];
        if ($platform == "ANDROID") {
            // for Android
            $fields = array(
                'to'  => $token,
                'notification' => $msg
            );
        } else if ($platform == "IOS") {
            // for iOS
            $fields = array(
                'to'  => $token,
                'notification' => $msg
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

        // $result =
        // {"multicast_id":6432239436887507566,"success":1,"failure":1,"canonical_ids":0,"results":[{"message_id":"0:1520827944262034%922280bf922280bf"},{"error":"InvalidRegistration"}]}

        logErrorDebug('sendPushFCM - result: ' . print_r($result, true));   
        return $result;
    }
    function client_signup(){
        logErrorDebug('client_singin - POST: ' . _json_encode($_POST));
        $data = $this->OldUser->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        $data = json_decode($data, true);
        $email = $data['email'];
        $registerType = $data['registerType'];
        $fcm_token = $data['token'];
        $fbuser = null;
        $fbuser_employees = null;
        $fbuser_customers = null;
        $id = null;
        $user = array();

        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');   
        $fbuser = $this->OldUser->queryWithOneResultSet('SELECT * FROM fbusers WHERE email = "' . $email . '"');
        $fbuser_employees = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE email = "' . $email . '"');
        $fbuser_customers = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE email = "' . $email . '"');
        if($registerType == 'signin'){
            $password = hash('sha256', $data['password']);
            if(!$fbuser){
                // If this is a staff member, there is no login account
                if($fbuser_employees){
                    $response['message'] = "Bạn là nhân viên của phòng nha, vui lòng liên hệ nhà quản trị để đăng kí tài khoản!";
                    $user = array();
                }
                // If this is a dental patient, there is no login account
                else if($fbuser_customers){
                    $new_password = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
                    $fbusers_insert = $this->OldUser->queryWithStatus('INSERT INTO fbusers (signin_date, email, password) VALUES (NOW(), "' . $email . '", "' . hash('sha256', $new_password) . '")');
                    if($fbusers_insert['status'] == 'OK'){
                        // Get customer id 
                        $customer_id = $fbuser_customers['id'];
                        // Get id role group
                        $role_group = $this->OldUser->queryWithOneResultSet('SELECT id FROM role_groups WHERE name = "'. USER_PATIENT_NAME . '"');
                        $role_group_id = $role_group['id'];
                        // Insert account in users tables
                        $users_insert = $this->OldUser->queryWithStatus('INSERT INTO users (username, password, role, role_group_id, customer_id) VALUES ("' . $email . '", "' . hash('sha256', $new_password) . '", 1, '. $role_group_id .', '.$customer_id.')');
                        $result =  Utility::send_mail_for_password($email, $new_password);
                        if($result['value'] == 1){
                            $response['message'] = "Bạn đã là bệnh nhân của phòng nha nhưng chưa có tài khoản, vui lòng kiểm tra email để nhận mật khẩu!";
                        }else{
                            $response['message'] = "Bạn đã là bệnh nhân của phòng nha nhưng chưa có tài khoản, vui lòng kiểm tra email để nhận mật khẩu, nếu không nhận được mail vui lòng sử dụng chức năng Quên Mật Khẩu!";
                        }
                    }else{
                        $response['message'] = "Lỗi hệ thống. Vui lòng thử lại!";    
                    }
                    $user = array();
                }else{
                    $response['message'] = "Email không tồn tại trong hệ thống!";   
                }
            }else if($fbuser){
                $fbuser = $this->OldUser->queryWithOneResultSet('SELECT * FROM fbusers WHERE email = "' . $email . '" AND password = "' . $password . '"'); 
                if($fbuser == null){
                    $response['message'] = "Email hoặc mật khẩu không đúng!";
                    $user = array();
                }else{
                    // Insert customer when active account from email
                    $customer_id =  $fbuser_customers['id'];
                    if(!$customer_id && !$fbuser_employees){
                        $date = getdate();
                        $code = $fbuser['clinic'].'_'.date("y").''.date("m").''.date("d").''.$date['hours'].''.date("i").''.date("s").'_BN';
                        $customers_insert = $this->OldUser->queryWithStatus('INSERT INTO customers (	clinic, cdate, first_name, last_name, country_pcode1, phone1, email, clinic_code, collaborator) VALUES ("'.$fbuser['clinic'].'", NOW(),"'.$fbuser['first_name'].'","'.$fbuser['last_name'].'", "'.$fbuser['country_code'].'","'.$fbuser['phone'].'","' . $email . '","'.$code.'", "' . COLLABORATOR_STATUS_NONE . '")');
                        // Get customer id
                        $id = $customers_insert['info']['id'];
                        // Get role group id
                        $role_group = $this->OldUser->queryWithOneResultSet('SELECT id FROM role_groups WHERE name = "'. USER_PATIENT_NAME . '"');
                        $role_group_id = $role_group['id'];
                        $users_insert = $this->OldUser->queryWithStatus('INSERT INTO users (username, password, role, role_group_id, customer_id) VALUES ("' . $email . '", "' . $password . '", "1", "'. $role_group_id .'", "' . $id . '")');
                        //create QR code
                        $tempDir = PRODUCT_IMAGE_PATH;
                        $codeContents = USER_PATIENT_NAME.'|'.$id;
                        require(SERVER_LIB_ROOT . '/phpqrcode/qrlib.php');
                        QRcode::png($codeContents, $tempDir.$code.'.png', QR_ECLEVEL_L, 3); 
                        $img = file_get_contents($tempDir.$code.'.png'); 
                        $base64 ='data:image/png;base64,'.base64_encode($img); 
                        $base64_result = $this->OldUser->queryWithStatus('UPDATE customers SET qr_code= "'.$base64.'" WHERE id = "' . $id . '"');
                        unlink($tempDir.$code.'.png');
                        // Encode the image string data into base64 
                    }
                    $checkToken = $this->OldUser->queryWithResultSet('SELECT * FROM fbusers WHERE fcm_token = "' . $fcm_token . '"');
                    if(count($checkToken['info']['rows']) > 0){
                        for ($i = 0 ; $i < count($checkToken['info']['rows']); $i++) {
                            $id =  $checkToken['info']['rows'][$i]['id'];
                            $nullToken = '';
                            $updateToken = $this->OldUser->queryWithStatus('UPDATE fbusers SET fcm_token = "' . $nullToken . '"  WHERE id = "' . $id . '"');
                        }
                    }
                    $result = $this->OldUser->queryWithStatus("UPDATE fbusers SET fcm_token='" . $fcm_token . "' WHERE id= '" . $fbuser['id'] ."'");
                    $id = $fbuser['id'];
                }  
            }else{
                $response['message'] = "Email hoặc mật khẩu không đúng!";
                $user = array();
            }
        }
        else{
            // Register an account with the dental room
            if (!$fbuser && !$fbuser_customers && !$fbuser_employees) {
                $latitude = 0.0;
                $longitude = 0.0;
                if($data['phone'][0] == '0'){
                    $data['phone'] = str_replace($data['phone'][0], '', $data['phone']);
                }
                $new_password = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
                $fbusers_insert = $this->OldUser->queryWithStatus('INSERT INTO fbusers (signin_date, clinic, email, first_name, last_name, country_code, phone, fcm_token, password) VALUES (NOW(), "' . $data['clinic'] . '", "' . $email . '", "' . Utility::firstCharString($data['first_name']) . '", "' . Utility::firstCharString($data['last_name']) . '", "' . $data['country_code'] . '", "'.$data['phone'].'", "' . $fcm_token . '","' . hash('sha256', $new_password) . '")');
                if($fbusers_insert['status'] == 'OK'){
                    $response['status'] = "OK";
                    $sendMail = Utility::send_mail_for_password($email, $new_password);
                    if($sendMail['value'] == 1){
                        $response['message'] = "Đăng ký thành công! Bạn vui lòng kiểm tra email để nhận mật khẩu.";
                    }else{
                        $response['message'] = "Đăng ký thành công! Nếu không nhận được email, bạn vui lòng sử dụng chức năng Quên Mật Khẩu để lấy lại mật khẩu!.";
                    }
                }else{
                    $response['message'] = "Lỗi hệ thống. Vui lòng thử lại!";         
                }
                $user = array();  
            } else if(!$fbuser){
                // If this is a staff member, there is no login account
                if($fbuser_employees){
                    $response['message'] = "Bạn là nhân viên của phòng nha, vui lòng liên hệ nhà quản trị để đăng kí tài khoản!";
                    $user = array();
                }
                // If this is a dental patient, there is no login account
                else if($fbuser_customers){
                    $new_password = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
                    $fbusers_insert = $this->OldUser->queryWithStatus('INSERT INTO fbusers (signin_date, email, password) VALUES (NOW(), "' . $email . '", "' . hash('sha256', $new_password) . '")');
                    // Get customer id
                    if($fbusers_insert['status'] == 'OK'){
                        $customer_id = $fbuser_customers['id'];
                        // Get id role group
                        $role_group = $this->OldUser->queryWithOneResultSet('SELECT id FROM role_groups WHERE name = "'. USER_PATIENT_NAME . '"');
                        $role_group_id = $role_group['id'];
                        // Insert users
                        $users_insert = $this->OldUser->queryWithStatus('INSERT INTO users (username, password, role, role_group_id, customer_id) VALUES ("' . $email . '", "' . hash('sha256', $new_password) . '", "1", "'. $role_group_id .'", "' .  $customer_id . '")');
                        $response['status'] = "OK";
                        $result = Utility::send_mail_for_password($email, $new_password);
                        if($result['value'] == 1){
                            $response['message'] = "Bạn đã là bệnh nhân của phòng nha nhưng chưa có tài khoản, vui lòng kiểm tra email để nhận mật khẩu!";
                        }else{
                            $response['message'] = "Bạn đã là bệnh nhân của phòng nha nhưng chưa có tài khoản, vui lòng kiểm tra email để nhận mật khẩu, nếu không nhận được email vui lòng sử dụng chức năng Quên Mật Khẩu";
                        }
                    }else{
                        $response['message'] = "Lỗi hệ thống. Vui lòng thử lại!";         
                    }
                    $user = array();
                }
            }
            // If the email is registered with the system
            else if($fbuser){
                $response['message'] = "Tài khoản đã tồn tại! Nếu bạn không nhớ mật khẩu, vui lòng sử dụng chức năng Quên Mật Khẩu.";
                $user = array();
            }
        }
        if($id != null){
            // now get user info for client
            $user = array('id' => $id, 'type' => array(), 'code' => '', 'short_name' => '', 'employee_id' => 0, 'customer_id' => 0, 'avatar' => '');       
            array_push($user['type'],array('value' =>USER_GUEST , 'name'=>USER_GUEST_NAME));
            // check user email
            $employee = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE email = "'. $email . '"');
            if ($employee == null) {
                // get user from customers
                $customer = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE email = "'. $email . '"');
                if ($customer == null) {
                    $response['message'] = "Đăng nhập thành công với email" .$data['email'];
                    $user['name'] =  $data['email'];
                } else {
                    $width = 5;
                    $customer_code = $customer['clinic_code'];
                    $user['code'] = $customer_code;
                    $user['customer_id'] = $customer['id'];
                    $user['name'] =  $customer['last_name'] .' '.$customer['first_name'];
                    $user['clinic'] =  $customer['clinic'];
                    $user['avatar'] = $customer['avatar'];
                    $response['message'] = "Chào " . $user['name'];
                    $user['type'] = array();
                    $user['qr_code'] =  $customer['qr_code'];
                    $user['collaborator'] =  $customer['collaborator'];
                    array_push($user['type'],array('value' =>USER_PATIENT , 'name'=>USER_PATIENT_NAME));
                }

            } else {
                $user['type'] = array();
                $positions = explode('|', $employee['position']);
                logErrorDebug('client_Signin - positions: ' . print_r($positions, true));  
                foreach ($positions as $p) {
                    if ($p == ES_ADMINISTRATOR) 
                        array_push($user['type'],array('value' =>USER_ADMINISTRATOR , 'name'=>ES_ADMINISTRATOR));
                    else if ($p == ES_MANAGER) 
                        array_push($user['type'],array('value' =>USER_MANAGER , 'name'=>ES_MANAGER));
                    else if ($p == ES_EREAL_MANAGER) 
                        array_push($user['type'],array('value' =>USER_EREAL_MANAGER , 'name'=>ES_MANAGER));
                    else if ($p == ES_CEO) 
                        array_push($user['type'], array('value' =>USER_CEO , 'name'=> $p));
                    else if($p == ES_DOCTOR)
                        array_push($user['type'], array('value' =>USER_DOCTOR , 'name'=> $p));
                    else if($p == ES_CONSULTING_DOCTOR)
                        array_push($user['type'], array('value' =>USER_CONSULTING_DOCTOR , 'name'=> $p));
                    else if( $p == ES_DOCTOR_IN_CHARGE_OF_EXPERTISE )
                        array_push($user['type'], array('value' =>USER_DOCTOR_IN_CHARGE_OF_EXPERTISE , 'name'=> $p));
                    else if( $p == ES_EXPERT_DOCTOR )
                        array_push($user['type'], array('value' =>USER_EXPERT_DOCTOR , 'name'=> $p));
                    else if( $p == ES_EREAL_DOCTOR_IN_CHARGE_OF_EXPERTISE )
                        array_push($user['type'], array('value' =>USER_EREAL_DOCTOR_IN_CHARGE_OF_EXPERTISE , 'name'=> $p));
                    else if ($p == ES_EREAL_EXPERT_DOCTOR) 
                        array_push($user['type'], array('value' =>USER_EREAL_EXPERT_DOCTOR , 'name'=> $p));
                    else if ($p == ES_ASSISTANT) 
                        array_push($user['type'],array('value' =>USER_ASSISTANT , 'name'=>ES_ASSISTANT));
                    else if ($p == ES_RECEPTIONIST)
                        array_push($user['type'],array('value' =>USER_RECEPTIONIST, 'name'=>ES_RECEPTIONIST));
                    else if ($p == ES_EREAL_HEAD_NURSE)
                        array_push($user['type'],array('value' =>USER_EREAL_HEAD_NURSE, 'name'=>ES_EREAL_HEAD_NURSE));
                    else if ($p == USER_VICE_PRESIDENT_NAME)
                        array_push($user['type'],array('value' =>USER_VICE_PRESIDENT, 'name'=>USER_VICE_PRESIDENT_NAME));
                    else if ($p == ES_EREAL_MAJOR_ACCOUNTING)
                        array_push($user['type'],array('value' =>USER_EREAL_MAJOR_ACCOUNTING, 'name'=>ES_EREAL_MAJOR_ACCOUNTING));
                    else if ($p == ES_HEAD_NURSE)
                        array_push($user['type'],array('value' =>USER_HEAD_NURSE, 'name'=>ES_HEAD_NURSE));
                    else if ($p == USER_SALES_MANAGER_NAME)
                        array_push($user['type'],array('value' =>USER_SALES_MANAGER, 'name'=>USER_SALES_MANAGER_NAME));
                    else if ($p == ES_CUSTOMER_CARE)
                        array_push($user['type'],array('value' =>USER_CUSTOMER_CARE, 'name'=>ES_CUSTOMER_CARE));
                    else if ($p == ES_MAKERTING)
                        array_push($user['type'],array('value' =>USER_MAKERTING, 'name'=>ES_MAKERTING));
                    else if ($p == ES_MAJOR_ACCOUNTING)
                        array_push($user['type'],array('value' =>USER_MAJOR_ACCOUNTING, 'name'=>ES_MAJOR_ACCOUNTING));
                    else if ($p == ES_ACCOUNTING)
                        array_push($user['type'],array('value' =>USER_ACCOUNTING, 'name'=>ES_ACCOUNTING));
                    else if ($p == ES_GENERAL_STAFF)
                        array_push($user['type'],array('value' =>USER_GENERAL_STAFF, 'name'=>ES_GENERAL_STAFF));
                    else if ($p == ES_COLLABORATOR)
                        array_push($user['type'],array('value' =>USER_COLLABORATOR, 'name'=>ES_COLLABORATOR));
                    else if ($p == ES_INSURANCE)
                        array_push($user['type'],array('value' =>USER_INSURANCE, 'name'=>ES_INSURANCE));
                    else if ($p == ES_LABO)
                        array_push($user['type'],array('value' =>USER_LABO, 'name'=>USER_LABOT_NAME));
                    else if ($p == ES_SUPPLY_SYSTEM)
                        array_push($user['type'],array('value' =>USER_SUPPLY, 'name'=>ES_SUPPLY_SYSTEM));     
                }

                logErrorDebug('position: ' . print_r($positions, true)); 
                $user['code'] = $employee['employee_code'];
                $user['name'] =  $employee['last_name'] .' '.$employee['first_name'];
                $user['short_name'] = $employee['short_name'];
                $user['employee_id'] = $employee['id'];
                $user['avatar'] = $employee['avatar'];
                $user['clinic'] = $employee['clinic'];
                $user['qr_code'] =  $employee['qr_code'];
                $user['collaborator'] =  true;
                if($employee['treatment_role'] == 1)
                    $user['treatment_role'] = true;
                else
                    $user['treatment_role'] = false;

                $response['message'] = "Xin chào " . $user['short_name'];
            }
            $response['status'] = 'OK';
        }
        $response['user'] = $user;            
        //logErrorDebug('client_Signin - response: ' . print_r($response, true));   
        _json_echo('client_signup', $response);
    }
    function sendMailForgotPassword(){
        require(SERVER_LIB_ROOT . '/PHPMailer/class.smtp.php');
        require(SERVER_LIB_ROOT . '/PHPMailer/class.phpmailer.php');
        require(SERVER_LIB_ROOT . '/PHPMailer/sendMail.php');

        $data = $this->OldUser->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        $data = json_decode($data, true);
        $email = $data['email'];
        $response = array('status' => 'ERROR', 'message' => 'none');  
        $new_password = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        $array = array();
        $fbuser = $this->OldUser->queryWithOneResultSet('SELECT * FROM fbusers WHERE email = "' . $email . '"'); 
        if($fbuser == null){
            $response['message'] = 'Email: ' . $email . 'chưa được đăng ký trong hệ thống!'; 
        }else{
            $response['status'] = 'OK';  
            $result =  Utility::send_mail_for_password($email, $new_password);
            if($result['value'] == 1){
                $fbusers = $this->OldUser->queryWithStatus("UPDATE fbusers SET password = '" . hash('sha256', $new_password) . "' WHERE id= '" . $fbuser['id'] ."'");
                $users = $this->OldUser->queryWithStatus("UPDATE users SET password = '" . hash('sha256', $new_password) . "' WHERE username= '" . $email ."'");
                $response['message'] = 'Mật khẩu mới đã được gởi tới email: ' .$email; 
            }else{
                $response['message'] = 'Không gửi được email'; 
                
            }
        }
        _json_echo('sendMailForgotPassword', $response);
    }
    function admin_save_password(){
       // set basic data
       $response = array('status' => 'ERROR', 'message' => 'none'); 
       $user_id = $this->OldUser->real_escape_string($_POST['id']);
       $oldPass = $this->OldUser->real_escape_string($_POST['old_password']);
       $newPass = $this->OldUser->real_escape_string($_POST['new_password']);
       $confirmPass = $this->OldUser->real_escape_string($_POST['rnew_password']);
       $user= $this->OldUser->queryWithOneResultSet('SELECT username FROM users WHERE id = '. $user_id .' AND password = "'. hash('sha256', $oldPass) .'"');
       logErrorDebug('notifications - client_getInfo: '. print_r($user, true));
       if ($user == null) {
        $response['message'] = 'Mật khẩu cũ không đúng!';
        } else {
            if ($newPass != $confirmPass) {
                $response['message'] = 'Mật khẩu mới không trùng với mật khẩu xác nhận!';
            } else {
                $user_email = $user['username'];
                $result = $this->OldUser->queryWithStatus('UPDATE fbusers SET password = "' . hash('sha256', $newPass) . '" WHERE email = "'. $user_email.'" ');          
                $result = $this->OldUser->queryWithStatus('UPDATE users SET password = "' . hash('sha256', $newPass) . '" WHERE id = '. $user_id);
                $response['status'] = 'OK';
                $response['message'] = 'Thay đổi mật khẩu thành công!'; 
            }
        }
        _json_echo('admin_save_password', $response);
    }
    function client_change_password() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        // no need to validate  
        $data = $this->OldUser->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        $data = json_decode($data, true);

        $oldPass = $data['oldPass'];
        $newPass = $data['newPass'];
        $confirmPass = $data['confirmPass'];   
        $id = $data['id']; ;   
        $user = $this->OldUser->queryWithOneResultSet('SELECT email FROM fbusers WHERE id = '. $id .' AND password = "'. hash('sha256', $oldPass) .'"');
        if ($user == null) {
            $response['message'] = 'Mật khẩu cũ không đúng!';
        } else {
            if ($newPass != $confirmPass) {
                $response['message'] = 'Mật khẩu mới không trùng với mật khẩu xác nhận!';

            } else {
                $result = $this->OldUser->queryWithStatus('UPDATE fbusers SET password = "' . hash('sha256', $newPass) . '" WHERE id = '. $id);
                $user_email = $user['email'];
                $result = $this->OldUser->queryWithStatus('UPDATE users SET password = "' . hash('sha256', $newPass) . '" WHERE username = "'. $user_email.'" ');
                $response['status'] = 'OK';
                $response['message'] = 'Thay đổi mật khẩu thành công!'; 
            }
        }
        _json_echo('client_change_password', $response);
    }
    function client_getNotifications() {
        // set basic data        
        $email = $this->OldUser->real_escape_string($_POST['email']);
        logErrorDebug('email - response: ' . $email);  
        // get all notification
        $notifications = $this->OldUser->queryWithResultSet('SELECT * FROM messages WHERE status != "' . STATUS_NOTI_ARCHIVE . '" AND email= "'. $email.'"  ORDER BY cdate DESC');
        logErrorDebug('notifications - client_getInfo: '. print_r($notifications, true));
         if ($notifications['status'] == 'OK') {
             $notifications = $notifications['info']['rows'];
             for ($i=0; $i < count($notifications); $i++) { 
                 $notifications[$i]['cdate'] = date('d/m/y H:i', strtotime($notifications[$i]['cdate']));
             }
             // set response
             $response['status'] = "OK";
             $response['info'] = $notifications;
         } else {
             $response['message'] = "Lỗi hệ thống. Yêu cầu truy cập lại sau!";
         }
         _json_echo('client_getNotifications', $response);
    }
    function client_deleteNotification() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none'); 
        $notification_id = $this->OldUser->real_escape_string($_POST['notifycation_id']);
        //logErrorDebug('notification_id - response: ' . $_POST['notification_id']); 
        // // update notification
        $notifications = $this->OldUser->queryWithStatus('DELETE FROM messages WHERE id = ' . $notification_id);
        logErrorDebug('notifications - client_getInfo: '. print_r($notifications, true));
        if ($notifications['status'] == 'OK') {
              // set response
            $response['status'] = "OK";
            $response['message'] = "Thông báo này đã bị xóa!";
        } else {
            $response['message'] = "Lỗi hệ thống. Yêu cầu truy cập lại sau!";
        }
        _json_echo('client_deleteNotification', $response);
    }
    function client_deleteAllNotification() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none'); 
        
        $email = $this->OldUser->real_escape_string($_POST['email']);
        logErrorDebug('email- response: ' . $_POST['email']); 
        // update notification
        $notifications = $this->OldUser->queryWithStatus('DELETE FROM messages WHERE email= "'. $email.'" ');
        //logErrorDebug('notifications - client_getInfo: '. print_r($notifications, true));
        if ($notifications['status'] == 'OK') {
              // set responses
            $response['status'] = "OK";
            $response['message'] = "Tất cả thông báo đã bị xóa!";
        } else {
            $response['message'] = "Lỗi hệ thống. Yêu cầu truy cập lại sau!";
        }
        _json_echo('client_deleteNotification', $response);
    }
    function client_getNumberNotification() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        $email = $this->OldUser->real_escape_string($_POST['email']);
        //logErrorDebug('emailgetNumberNotification - response: ' . $email);  
         $number_badge = 0;
         $query = 'SELECT COUNT(*) as badge FROM messages WHERE status = "' . STATUS_NOTI_UNREAD . '" AND email= "'. $email.'"';
         $result = $this->OldUser->queryWithOneResultSet($query);
            //logErrorDebug('emailgetNumberNotification - response: ' . print_r($result, true)); 
           if ($result) {
             $number_badge = $result['badge'];
           }
           $response['status'] = "OK";
           $response['info'] = $number_badge;

          _json_echo('client_getNumberNotification', $response);
    }
    function client_readNotification() {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        
        $notification_id = $this->OldUser->real_escape_string($_POST['notifycation_id']);
        //logErrorDebug('$notification_id - response: ' . $notification_id);  
        // update notification
        $notifications = $this->OldUser->queryWithStatus('UPDATE messages SET status = ' . STATUS_NOTI_READ . ' WHERE id = ' .$notification_id);
        //logErrorDebug('emailgetNumberNotification - response: ' . print_r($notifications, true)); 
        if ($notifications['status'] == 'OK') {
            // set response
            $response['status'] = "OK";  
        } else {
            $response['message'] = "Lỗi hệ thống. Yêu cầu truy cập lại sau!";
        }
        _json_echo('client_readNotification', $response);
    }
    function sendSMS(){
        require(SERVER_LIB_ROOT . '/PHPSendSMS/sendSMS.php');
        $response = array('status' => 'ERROR', 'message' => 'none');
        $phone = $this->OldUser->real_escape_string($_POST['phone']);
        $content = $this->OldUser->real_escape_string($_POST['message']);
        $resul = sendSMS($phone, $content);

        if($resul['CodeResult']==100)
           $response = array('status' => 'OK', 'message' => 'gửi tin nhắn thành công');
        else
            $response = array('status' => 'ERROR', 'message' => 'gửi tin nhắn thất bại');

        _json_echo('sendSMS', $response);
    }
    function client_getInfoQR(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $id = $this->OldUser->real_escape_string($_POST['data']);
        $id = explode("|", $id);
        if($id[0]== USER_PATIENT_NAME){
            $info_result = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE id = ' . $id[1] . '');
            logErrorDebug('clinics - clinics: '. print_r($info_result, true));
            if($info_result != ''){
                if($info_result['dob'] != '0000-00-00 00:00:00')
                    $info_result['dob'] = date( 'd/m/Y (H:i)', strtotime( $info_result['dob']));
                else $info_result['dob'] = '';
                if($info_result['address'] != '' && $info_result['district'] != '')
                    $address = $info_result['address'].','.$info_result['district'].','.$info_result['city'];
                else if($info_result['address'] == '' && $info_result['district'] != '')
                    $address = $info_result['district'].','.$info_result['city'];
                else if($info_result['district'] == '' && $info_result['address'] != '')
                    $address = $info_result['address'].','.$info_result['city'];
                else
                    $address =$info_result['city'];
                $info = array(
                    'type' => USER_PATIENT_NAME,
                    'id' => $info_result['id'],
                    'name' => $info_result['last_name'].' '.$info_result['first_name'],
                    'clinic_code' => $info_result['clinic_code'],
                    'dob' => $info_result['dob'],
                    'address' => $address,
                    'phone' => '(+'. $info_result['country_pcode1'].')'. $info_result['phone1'],
                    'email' => $info_result['email'],
                    'status' => $info_result['status'],
                    'country_pcode1' => $info_result['country_pcode1']
                );
                $response = array('status' => 'OK', 'message' => '', 'info' => $info);
            }
        }else if($id[0]== USER_EMPLOYEES_NAME){
            $info_result = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE id = ' . $id[1] . '');
            if($info_result != ''){
                if($info_result['dob'] != '0000-00-00 00:00:00')
                    $info_result['dob'] = date( 'd/m/Y (H:i)', strtotime( $info_result['dob']));
                else $info_result['dob'] = '';
                if($info_result['address'] != '' && $info_result['district'] != '')
                $address = $info_result['address'].','.$info_result['district'].','.$info_result['city'];
                else if($info_result['address'] == '' && $info_result['district'] != '')
                    $address = $info_result['district'].','.$info_result['city'];
                else if($info_result['district'] == '' && $info_result['address'] != '')
                    $address = $info_result['address'].','.$info_result['city'];
                else
                $address =$info_result['city'];
                $info = array(
                    'type' => USER_EMPLOYEES_NAME,
                    'id' => $info_result['id'],
                    'name' => $info_result['last_name'].' '.$info_result['first_name'],
                    'clinic_code' => $info_result['employee_code'],
                    'dob' => $info_result['dob'],
                    'address' => $address,
                    'phone' => '(+'. $info_result['country_pcode1'].')'. $info_result['phone1'],
                    'email' => $info_result['email'],
                    'country_pcode1' => $info_result['country_pcode1']
                );
                $response = array('status' => 'OK', 'message' => '', 'info' => $info);
            }
        }
        
        _json_echo('client_getInfoQR', $response);
    }
    function client_getClinics(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $clinics = $this->OldUser->queryWithResultSet('SELECT * FROM clinics');
        logErrorDebug('clinics - clinics: '. print_r($clinics, true));
        $clinics = $clinics['info']['rows'];
        $response = array('status' => 'OK', 'message' => '', 'clinics' => $clinics);
        _json_echo('client_getClinics', $response);
    }
    function client_getCountries(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $countries = $this->OldUser->queryWithResultSet('SELECT code as "label", code as "value" FROM countries');
        $countries = $countries['info']['rows'];
        $response = array('status' => 'OK', 'message' => '', 'countries' => $countries);
        _json_echo('client_getCountries', $response);
    }
    function client_getNameCountries(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $countries = $this->OldUser->queryWithResultSet('SELECT name, code FROM countries');
        $countries = $countries['info']['rows'];
        $response = array('status' => 'OK', 'message' => '', 'countries' => $countries);
        _json_echo('client_getCountries', $response);
    }
    function client_getInfoUser(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $id = $this->OldUser->real_escape_string($_POST['id']);
        $type = $this->OldUser->real_escape_string($_POST['type']);
        if($type == USER_PATIENT_NAME){
            $info_result = $this->OldUser->queryWithOneResultSet('SELECT * FROM customers WHERE id = ' . $id . '');
            $info_result['dob'] = date( 'Y-m-d', strtotime( $info_result['dob']));
            $info_result['systemic_diseases'] = explode('|', $info_result['systemic_diseases']);
            $info_result['dental_diseases'] = explode('|', $info_result['dental_diseases']);
        } else{
            $info_result = $this->OldUser->queryWithOneResultSet('SELECT * FROM employees WHERE id = ' . $id . '');
            $info_result['dob'] = date( 'Y-m-d', strtotime( $info_result['dob']));
        }
        $dentalDiseases = $this->OldUser->queryWithResultSet('SELECT * FROM c_dental_diseases');
        $systemicDiseases = $this->OldUser->queryWithResultSet('SELECT * FROM c_systemic_diseases');
        $response = array('status' => 'OK', 'message' => '', 'info' => $info_result, 'dentalDiseases' => $dentalDiseases['info']['rows'], 'systemicDiseases' => $systemicDiseases['info']['rows']);
        _json_echo('client_getInfoUser', $response);
    }
    function client_setInfoUser(){
        $response = array('status' => 'ERROR', 'message' => 'none');
        $email = $this->OldUser->real_escape_string($_POST['email']);
        $email = str_replace(" ","",$email);
        $id = $this->OldUser->real_escape_string($_POST['id']);
        $type = $this->OldUser->real_escape_string($_POST['type']);
        $last_name = $this->OldUser->real_escape_string($_POST['last_name']);
        $first_name = $this->OldUser->real_escape_string($_POST['first_name']);
        $dob = $this->OldUser->real_escape_string($_POST['dob']);
        $gender = $this->OldUser->real_escape_string($_POST['gender']);
        $country = $this->OldUser->real_escape_string($_POST['country']);
        $address = $this->OldUser->real_escape_string($_POST['address']);
        $district = $this->OldUser->real_escape_string($_POST['district']); 
        $city = $this->OldUser->real_escape_string($_POST['city']);
        $phone = $this->OldUser->real_escape_string($_POST['phone']);
        $oldEmail = $this->OldUser->real_escape_string($_POST['oldEmail']);
        if($phone[0] == '0'){
            $phone = str_replace($phone[0], '', $phone);
        }
        $country_pcode = $this->OldUser->real_escape_string($_POST['country_pcode']);
        $dentalDiseases = $this->OldUser->real_escape_string($_POST['dentalDiseases']);
        if($dentalDiseases == '[]'){
            $dentalDiseases = 'Không';
        }else{
            $dentalDiseases = str_replace('\"', '"', $dentalDiseases);
            $dentalDiseases = json_decode($dentalDiseases, true);
            $provisional = array(); 
            foreach ($dentalDiseases as $value){
                array_push($provisional, $value['name']);
            }
            $dentalDiseases = implode('|', $provisional );
        }
        
        $systemicDiseases = $this->OldUser->real_escape_string($_POST['systemicDiseases']);
        if($systemicDiseases == '[]'){
            $systemicDiseases = 'Không';
        }else{ 
            $systemicDiseases = str_replace('\"', '"', $systemicDiseases);
            $systemicDiseases = json_decode($systemicDiseases, true);
            $provisional1 = array(); 
            foreach ($systemicDiseases as $value){
                array_push($provisional1, $value['name']);
            }
            $systemicDiseases = implode('|', $provisional1 );
        }
        $email_customers_result = $this->OldUser->queryWithResultSet('SELECT email FROM customers WHERE email = "' . $email . '" AND id != "' . $id . '"');
        $email_employees_result = $this->OldUser->queryWithResultSet('SELECT email FROM employees WHERE email = "' . $email . '" AND id != "' . $id . '"');
        
        if($email_customers_result['info']['rows'] != null || $email_employees_result['info']['rows'] != null){
            $response['message'] = 'Email đã tồn tại!';
        }else{
            if($type == USER_PATIENT_NAME){
                $result = $this->OldUser->queryWithStatus('UPDATE customers SET email = "' . $email . '", last_name = "' . $last_name . '", first_name = "' . $first_name . '", dob = "' . $dob . '",
                gender = "' . $gender . '", country = "' . $country . '", address = "' . $address . '", district = "' . $district . '", city = "' . $city . '", 
                phone1 = "' . $phone . '", country_pcode1 = "' . $country_pcode . '", dental_diseases = "' . $dentalDiseases . '", systemic_diseases = "' . $systemicDiseases . '" WHERE id = "' . $id . '"');
                
                $users_result = $this->OldUser->queryWithStatus('UPDATE users SET username= "'.$email.'" WHERE username = "' . $oldEmail . '"');
                $fbusers_result = $this->OldUser->queryWithStatus('UPDATE fbusers SET email= "'.$email.'" WHERE email = "' . $oldEmail . '"');
                $response = array('status' => 'OK', 'message' => 'none');
                //logErrorDebug('email_result - email_result: '. print_r($result, true));
            }else{
                $result = $this->OldUser->queryWithStatus('UPDATE employees SET email = "' . $email . '", last_name = "' . $last_name . '", first_name = "' . $first_name . '", dob = "' . $dob . '",
                gender = "' . $gender . '", country = "' . $country . '", address = "' . $address . '", district = "' . $district . '", city = "' . $city . '", 
                phone1 = "' . $phone . '", country_pcode1 = "' . $country_pcode . '" WHERE id = "' . $id . '"');

                $users_result = $this->OldUser->queryWithStatus('UPDATE users SET username= "'.$email.'" WHERE username = "' . $oldEmail . '"');
                $fbusers_result = $this->OldUser->queryWithStatus('UPDATE fbusers SET email= "'.$email.'" WHERE email = "' . $oldEmail . '"');
                $response = array('status' => 'OK', 'message' => 'none');
            } 
        }
        //logErrorDebug('email_result - email_result: '. print_r($email_customers_result, true));
        _json_echo('client_getInfoUser', $response);
    }
    function client_getUsers(){
        //logErrorDebug('client_getPatients - POST: ' . _json_encode($_POST));
        $response = array('status' => 'ERROR', 'message' => 'none');  
        $clinics = $this->OldUser->real_escape_string($_POST['clinic']);
        $clinics = explode('|', $clinics);
        $patients = $this->OldUser->queryWithResultSet('SELECT customers.id, customers.clinic, customers.first_name, customers.last_name, customers.email, customers.phone1, customers.country_pcode1, customers.avatar FROM customers JOIN fbusers ON customers.email = fbusers.email');
        $employees = $this->OldUser->queryWithResultSet('SELECT employees.id, employees.clinic, employees.first_name, employees.last_name, employees.email, employees.phone1, employees.country_pcode1, employees.avatar, employees.position FROM employees JOIN fbusers ON employees.email = fbusers.email');
        $userList = array();
        foreach($employees['info']['rows'] as $employee) {
            $clinic = $employee['clinic'];
            $employee['value'] = $employee['first_name'];
            $employee['type'] = $employee['position'];
            if($employee['avatar'] != '')
                $employee['avatar'] = APP_PATH . '/' . 'share/config/images' . '/' . 'product' . '/' .''. $employee['avatar']; 
            if(array_search($clinic ,$clinics) > -1)
            {
                array_push($userList, $employee);
            }
        }
        foreach($patients['info']['rows'] as $patient) {
            $clinic = $patient['clinic'];
            $patient['value'] = $patient['first_name'];
            $patient['type'] = USER_PATIENT_NAME;
            if($patient['avatar'] != '')
                $patient['avatar'] = $patient['avatar']; 
            if(array_search($clinic ,$clinics) > -1)
            {
                array_push($userList, $patient);
            }
        }     
        $response = array('status' => 'OK', 'message' => 'none', 'userList' => $userList); 
        _json_echo('client_getPatients', $response);
    }
    function afterAction() {
	
	}
}