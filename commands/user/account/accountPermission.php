<?php

class accountPermission {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getUserInformation':
                $this->getUserInformation();
                break;
            case 'getEmployeeList':
                $this->getEmployeeList();
                break;
            case 'getCustomerList':
                $this->getCustomerList();
                break;
            case 'setUserPermission':
                $this->setUserPermission();
                break;
            case 'getModulesChain':
                $this->getModulesChain();
                break;
            case 'saveUserInformation':
                $this->saveUserInformation();
                break;
            case 'deleteFormData':
                $this->deleteFormData();
                break;
            case 'getPermissionTemplate':
                $this->getPermissionTemplate();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action ' .$action);
                _json_echo('accountPermission', $response);
        }
    }
    function getUserInformation(){
        $response = array('status' => 'ERROR', 'message' => 'getUserInformation', 'data' => array());
        $postData = Utility::processedData();
        $table = 'users';
        $query = '
            SELECT 
                users.*,
                customers.last_name,
                customers.first_name,
                employees.last_name,
                employees.first_name
            FROM users
            LEFT JOIN employees ON users.employee_id = employees.id
            LEFT JOIN customers ON users.customer_id = customers.id
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
        $adata = array();
        foreach($result['data'] as $rs){
            $rs['User']['app_permission_customer'] = json_decode($rs['User']['app_permission_customer'], true);
            $rs['User']['app_permission_employee'] = json_decode($rs['User']['app_permission_employee'], true);
            $adata[] =  array(
                'DT_RowId' =>'row_'.$rs['User']['id'],
                'users' => $rs['User'],
                'employees' => $rs['Employee'],
                'customers' => $rs['Customer']
            );
        }
        $response = array(
            "draw" => intval($result['draw']),
            "data" => $adata,
            "options" => array(),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['totalRecordwithFilter']
        );
        _json_echo('getUserInformation', $response);
    }
    function saveUserInformation(){
        $response = array('status' => 'ERROR', 'message' => 'saveUserInformation', 'data' => array(), 'errorData' => array());
        $error = false;
        $data = Utility::processedData();
        $data['email'] = strtolower(trim($data['email']));
        $data['password'] = hash('sha256', $data['password']);
        $data['fcm_token'] = '';
        $condition = '';
        $permission = json_encode(array(), true);
        if(!empty($data['email']) && !empty($data['phone'])){
            $condition = 'email = "'. $data['email'] . '" OR phone = "' . $data['phone'] . '"';
        }else if(!empty($data['email'])){
            $condition =  'email = "'. $data['email'] . '"';
        }else if(!empty($data['phone'])){
            $condition =  'phone = "'. $data['phone'] . '"';
        }
        $sql = 'SELECT * FROM users WHERE ' .$condition;
        $user_existing = $this->sql_model()->queryWithResultSet($sql);
        if (count($user_existing['info']['rows']) > 0) {
            if($user_existing['info']['rows'][0]['email']){
                $response['errorData']['email'] = $data['email'];
            }else{
                $response['errorData']['phone'] = $data['phone'];
            }
            $error = true;    
        } 
        if (!$error) {
            $skip = array();
            $data['app_permission_customer'] = json_encode(array(), true);
            $data['app_permission_employee'] = json_encode(array(), true);
            if( $data['employee_id'] > 0 ){
                $id = $data['employee_id'];
                $type = 'employee';
                // $permission = $this->getPermissionTemplateForCreateUser( $id, $type );
                $data['app_permission_employee'] = json_encode(array(), true);
            }else if($data['customer_id'] > 0) {
                $id = $data['customer_id'];
                $type = 'customers';
                $permission = $this->getPermissionTemplateForCreateUser( $id, $type );
                $data['app_permission_customer'] = $permission;
            }
            $result = Utility::processedSaveData('users', $data, $skip);
            if($result['status'] == "OK"){
                $response['status'] = 'OK';
                $response['id'] = $result['info']['id'];
            } 
        }
        _json_echo('saveUserInformation', $response);
    }

    function getPermissionTemplateForCreateUser( $id, $type ){
        $code = '';
        if( $type == 'customers' ){
            $customers = $this->sql_model()->queryWithOneResultSet('SELECT* FROM customers WHERE id = "'. $id .'"');
            if( $customers['type'] == 1 ){
                $code = 'DOCTORS';
            }else if( $customers['type'] == 2 ){
                $code = 'CLINICS';
            }else if( $customers['type'] == 3 ){
                $code = 'COMPANY';
            }
            $permission_template = $this->sql_model()->queryWithOneResultSet('SELECT* FROM c_permission_template WHERE code = "'. $code .'"');
            $permission = $permission_template['permissions'];
            return $permission;
        }else {
            $code = 'EMPLOYEE';
            $permission_template = $this->sql_model()->queryWithOneResultSet('SELECT* FROM c_permission_template WHERE code = "'. $code .'"');
            $permission = $permission_template['permissions'];
            return $permission;
        }
    }

    function getEmployeeList(){
        $data = Utility::processedData();
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $str_empty = ' ';
        $customers = $this->sql_model()->queryWithResultSet('
            SELECT id, code, last_name, first_name, email, country_pcode1, phone1 FROM employees
            WHERE 
            NOT EXISTS
			(
                SELECT email, phone FROM users
                WHERE 
                (users.email = employees.email AND users.email NOT LIKE "") 
                OR 
                (users.phone = concat(employees.country_pcode1, employees.phone1) AND users.phone  NOT LIKE "")
            ) 
            AND 
            (   
                concat(last_name, first_name) LIKE "%'.$data['term'].'%" OR 
                concat(last_name, "'.$str_empty.'", first_name) LIKE "%'.$data['term'].'%"
            )
            LIMIT '.$end.','.$start.'
        ');
        $count = count($customers['info']['rows']);
        $data = array();
        if($count > 0){
            foreach($customers['info']['rows'] as $cs){
                $data[] = array(
                    'id'=>$cs['id'].'|'.$cs['email'].'|'.$cs['country_pcode1'].$cs['phone1'],
                    'name'=>$cs['last_name'] . ' ' . $cs['first_name'] . ' (' . $cs['code'] . ')', 'total_count'=>$count ); 
            }
        }
        _json_echo('admin_getCustomers', $data);
    }
   
    function getCustomerList() {
        $data = Utility::processedData();
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $customers = $this->sql_model()->queryWithResultSet('
            SELECT id, code, last_name, first_name, email, country_pcode1, phone1 FROM customers
            WHERE 
            NOT EXISTS
            (
               SELECT email, phone FROM users
               WHERE 
               (users.email = customers.email AND users.email NOT LIKE "") 
               OR 
               (users.phone = concat(customers.country_pcode1, customers.phone1) AND  users.phone  NOT LIKE "")
            )
            AND concat(customers.last_name, customers.first_name) LIKE "%'.$data['term'].'%" LIMIT '.$end.','.$start.'
        ');
        $count = count($customers['info']['rows']);
        $data = array();
        if($count > 0){
            foreach($customers['info']['rows'] as $cs){
                $data[] = array(
                    'id'=>$cs['id'].'|'.$cs['email'].'|'.$cs['country_pcode1'].$cs['phone1'],
                    'name'=>$cs['last_name'] . ' ' . $cs['first_name'] . ' (' . $cs['code'] . ')', 
                    'total_count'=>$count 
                ); 
            }
        }
        _json_echo('getCustomerList', $data);
    }
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('users', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('deleteFormData', $response);
    }
    function getModulesChain(){
        $mod = 'global';
        $route = 'chain';
        $controller = 'chainGlobal';
        $action = 'getModulesChain';
        Utility::callLocalFunction($mod, $route, $controller,$action);
    }
    function setUserPermission(){
        $mod = 'global';
        $route = 'user';
        $controller = 'userGlobal';
        $action = 'setUserPermission';
        Utility::callLocalFunction($mod, $route, $controller,$action);
    } 
    function getPermissionTemplate(){
        $response = array('status' => 'ERROR', 'message' => 'getPermissionTemplate', 'data' => array());
        $data = Utility::processedData();
        $permission = $this->sql_model()->queryWithResultSet('
            SELECT c_permission_template.*
            FROM c_permission_template
        ');
        if( $permission['status'] == 'OK' ){
            foreach( $permission['info']['rows'] as $item ){
                $response['data'][] = array(
                    'value' => json_decode($item['permissions'], true),
                    'label' => $item['name']
                );
            }
            $response['status'] = 'OK';
        }
        _json_echo('getPermissionTemplate', $response);
    }
    function __destruct() {
    }
}
