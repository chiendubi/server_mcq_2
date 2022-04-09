<?php

class information {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getInformation':
                $this->getInformation();
                break;
            case 'getOptionData':
                $this->getOptionData();
                break;
            case 'changeCity':
                $this->changeCity();
                break;
            case 'getInformationFormData':
                $this->getInformationFormData();
                break;
            case 'saveInformationFormData':
                $this->saveInformationFormData();
                break;
            case 'getClinicsData':
                $this->getClinicsData();
                break;
            case 'getCustomersById':
                $this->getCustomersById();
                break;
            case 'getCountry':
                $this->getCountry();
                break;
            case 'processParentId':
                $this->processParentId();
                break;

            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action');
                _json_echo('information', $response);
        }
    }

    function getInformation() {
        $response = array('status' => 'ERROR', 'message' => 'none', 'data'=>array());
        $data = Utility::processedData();
        $id = $data['user_id'];
        $user = $this->sql_model()->queryWithOneResultSet('SELECT * FROM users WHERE id = ' . $id);
        $customer_id = $user['customer_id'];
        $employee_id = $user['employee_id'];
        $chain_code = $user['chain_code'];
        $user_information = array();
        $chainPermission = connect_core::sqlQuery('SELECT app_permission, short_name FROM chain WHERE code ="'.$chain_code.'"');
        $chainPermission = isset($chainPermission['info']['rows']) ? $chainPermission['info']['rows'][0] : array();
        $chain = array(
            'app_permission' => (count($chainPermission) > 0 ) ? $chainPermission['app_permission'] : array(),
            'short_name' => (count($chainPermission) > 0) ? $chainPermission['short_name'] : ''
        );
        if ($user['customer_id'] > 0){
            $permission_arr = array();
            $permission = json_decode($user['app_permission_customer'], true);
            if(count($permission) > 0){
                if(count($chainPermission) > 0){
                    $modules = json_decode($chain['app_permission'], true);
                    foreach($permission as $per){
                        if(array_search($per, $modules) > -1){
                            $permission_arr[$per] = true;
                        }
                    }
                }
            }
            $customer = $this->sql_model()->queryWithOneResultSet('SELECT * FROM customers WHERE id = ' . $customer_id);
            $parent_id = json_decode($customer['parent_id'], true);
            $affiliate_confirm = is_array(json_decode($customer['affiliate_confirm'], true)) ? json_decode($customer['affiliate_confirm'], true) : array();
            $affiliate_cancel = is_array(json_decode($customer['affiliate_cancel'], true)) ? json_decode($customer['affiliate_cancel'], true) : array();
            $branch = array();
            ## $clinics = explode('|' , $customer['c_clinic_code']);
            ## $parameter = array('code' => $clinics);
            ## Utility::processedAddParameterToPost($parameter);
            ## $clinics = Utility::getOptionDynamic('global', 'clinic', 'clinicGlobal', 'getInfoClinicByCode');
            $user_information[] = array(
                'action_code' => 'ESCP',
                'id' => $customer['id'],
                'code' => $customer['code'],
                'avatar' => $customer['avatar'],
                'countryCode' => $customer['country_pcode1'],
                'district' => $customer['district'],
                'city' => $customer['city'],
                'country' => $customer['country'],
                'name' => $customer['last_name'] . ' ' . $customer['first_name'],
                'position' => USER_CUSTOMER_NAME,
                'c_branch_code' => $branch,
                'permissions' => $permission_arr,
                'chain' => $chain_code,
                'type' => $customer['type'],
                'parent_id' => $parent_id,
                'affiliate_confirm' => $affiliate_confirm,
                'affiliate_cancel' => $affiliate_cancel,
                'chain_short_name' => $chain['short_name'],
            );
        }
        if($user['employee_id'] > 0){
            $permission_arr = array();
            $permission = json_decode($user['app_permission_employee'], true);
            if(count($permission) > 0){
                if(count($chainPermission) > 0){
                    $modules = json_decode($chain['app_permission'], true);
                    foreach($permission as $per){
                        if(array_search($per, $modules) > -1){
                            $permission_arr[$per] = true;
                        }
                    }
                }
            }
            $employee = $this->sql_model()->queryWithOneResultSet('SELECT * FROM employees WHERE id = ' . $employee_id);
            $branch = array();
            ## $clinics = explode('|' , $employee['c_branch_code']);
            ## $parameter = array('code' => $clinics);
            ## Utility::processedAddParameterToPost($parameter);
            ## $branch = Utility::getOptionDynamic('global', 'clinic', 'clinicGlobal', 'getInfoClinicByCode');
            $user_information[] = array(
                'action_code' => 'ESEP',
                'id' => $employee['id'],
                'code' => $employee['code'],
                'avatar' => $employee['avatar'],
                'name' => $employee['last_name'] . ' ' . $employee['first_name'],
                'position' => $employee['c_position_code'],
                'c_branch_code' => $branch,
                'permissions' => $permission_arr,
                'chain' => $chain_code,
                'type' => "0",
                'chain_short_name' => $chain['short_name'],
            );
        }
        $response['status'] = 'OK';
        $response['message'] = "Log in successful!";
        $response['data'] = $user_information;
        _json_echo('admin_getUserName', $response);
    }
    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $data = Utility::processedData();
        $response['data'] = array(
            'country' => Utility::getOptionDynamic('global', 'country', 'countryGlobal', 'getCountry'),
            'cities' => Utility::getOptionDynamic('global', 'cities', 'citiesGlobal', 'getCities'),
            'company' => $this->getCompany()
        );
        _json_echo('getOptionData', $response);
    }
    
    function changeCity(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $data = Utility::processedData();
        $sql_model = new VanillaModel();
        $district =  $sql_model->queryWithResultSet('
            SELECT c_districts.*
            FROM c_districts
            Where city_name = "'. $data['name'] .'"
        ');
        if(count($district['info']['rows']) > 0){
            foreach($district['info']['rows'] as $dt){
                $response['data'][] = array(
                    'value' => $dt['name'],
                    'label' => $dt['name']
                );
            }
        }
        _json_echo('getOptionData', $response);
    }

    function getCompany(){
        $sql_model = new VanillaModel();
        $response = array('data'=> array());
        $methods =  $sql_model->queryWithResultSet('
            SELECT customers.*
            FROM customers
            Where type = 3
        ');
        if(count($methods['info']['rows']) > 0){
            foreach($methods['info']['rows'] as $dt){
                $response['data'][] = $dt;
            }
        }
        return $response['data'];
    }

    function getInformationFormData(){
        $response = array('status' => 'ERROR', 'message' => 'none', 'data'=>array());
        $data = Utility::processedData();
        if( $data['type'] != 0 ){
            $id = $data['id'];
            $action_code = $data['action_code'];
            $table = 'employees';
            if($action_code == 'ESCP'){
                $table = 'customers';
            }
            $result = $this->sql_model()->queryWithOneResultSet('
                SELECT '. $table .'.* 
                FROM '. $table .' 
                WHERE id = ' . $id . '
            ');
            $result['affiliate_confirm'] = is_array(json_decode($result['affiliate_confirm'], true)) ? json_decode($result['affiliate_confirm'], true) : array();
            $result['affiliate_cancel'] = is_array(json_decode($result['affiliate_cancel'], true)) ? json_decode($result['affiliate_cancel'], true) : array();
            if( !empty($data['parent_id']) ){
                $parent_id = empty($data['parent_id']) ? '' : $data['parent_id'][0] ;
                $parent = array();
                if( $parent_id != "" ){
                    $parent = $this->sql_model()->queryWithOneResultSet('
                        SELECT '. $table .'.* 
                        FROM '. $table .' 
                        WHERE id = ' . $parent_id . '
                    ');
                }
            }else{
                $parent = '';
            }
            if($result){
                $response['status'] = 'OK';
                $response['data'] = array(
                    'clinic' => $result,
                    'parent' => $parent
                );
            }
        }else{
            $result = $this->sql_model()->queryWithOneResultSet('
                SELECT employees.* 
                FROM employees 
                WHERE id = ' . $data['id'] . '
            ');
            if($result){
                $response['status'] = 'OK';
                $response['data'] = array(
                    'clinic' => $result,
                    'parent' => ''
                );
            }
        }
        _json_echo('getInformationFormData', $response);
    }

    function saveInformationFormData(){
        $response = array('status' => 'ERROR', 'message' => 'saveInformationFormData', 'data'=>array());
        require_once(SERVER_ROOT . '/commands/global/user/userGlobal.php');
        $data = Utility::processedData(true);
        if( $data['other']['type'] != 0 ){
            $result = $this->processSaveFormData($data['data'], $data['other']);
            if($result['status'] == 'OK'){
                $response['status'] = "OK";
            }else{
                $response['status'] = $result['error'][0]["status"];
            }
        }else{
            $skip = array('cdate');
            $employee = $data['data'];
            $result = Utility::processedSaveData('employees', $employee, $skip);
            if( $result['status'] == "OK" ){
                $skip_user = array();
                $response['status'] = "OK";
                $result_user_id = $this->sql_model()->queryWithOneResultSet('
                    SELECT users.* 
                    FROM users 
                    WHERE employee_id = ' . $data['data']['id'] . '
                ');
                $user['id'] = $result_user_id['id'];
                $user['email'] = $data['data']['email'];
                $user['phone'] = $data['data']['phone1'];
                $result = Utility::processedSaveData('users', $user, $skip_user);
            }
        }
        _json_echo('saveInformationFormData', $response);
    }

    function processSaveFormData($item, $other){
        $response = array('status' => 'ERROR', 'message' => 'processSaveFormData', 'data' => array(), 'error' => array());
        $item['cdate'] = date('Y-m-d H:i:s');
        $item['last_name'] = Utility::firstCharString($item['last_name']);
        $item['first_name'] = Utility::firstCharString($item['first_name']);
        ## Check email and phone exist (user)
        $userExist = userGlobal::processUserCheck('customers', $item['email'], $item['country_pcode1'], $item['phone1'], $item['id'], null);
        if(!$userExist['fieldError']){
            ## Using skip for update not using insert
            $skip = array();
            if($item['id'] > 0){
                $skip = array('cdate');
            }
            $result = Utility::processedSaveData('customers', $item, $skip);
            if($result['status'] == "OK"){
                ## Create QR code
                $item_id = $item['id'] > 0 ? $item['id'] : $result['info']['id'];
                if( !isset($qrData) ){
                    $qrData = array();
                    $qrData['id'] = $item_id;
                    $qr_name = $item['code'];
                    $content = 'CUSTOMER|'.$qrData['id'];
                    $qrData['qr_code'] = Utility::create_qr($qr_name, $content);
                    $skip = array();
                    $result = Utility::processedSaveData('customers', $qrData, $skip);
                }
                if( $result['status'] == "OK" ){
                    ## Create or update account inforamation
                    $action = $item['id'] > 0 ? 'edit' : 'create';
                    $code = '';
                    if( $action == 'create' ){
                        if( $item['type'] == 1 ){
                            $code = 'DOCTORS';
                        }else if( $item['type'] == 2 ){
                            $code = 'CLINICS';
                        }else if( $item['type'] == 3 ){
                            $code = 'COMPANY';
                        }
                        $permission_template = $this->sql_model()->queryWithOneResultSet('SELECT* FROM c_permission_template WHERE code = "'. $code .'"');
                        $permission = $permission_template['permissions'];
                    }else {
                        $permission = '';
                    }
                    userGlobal::processUserCRUD('customers', $action, $item['email'], $item['country_pcode1'], $item['phone1'], $item_id, $other['chain_code'], $permission);
                }
                $response['status'] = 'OK';
                $response['data'] = $item_id;
            }
        }else{
            $response['error'] = $userExist['dataError']; 
        }
        return $response;
    }

    function getClinicsData(){
        $sql_model = new VanillaModel();
        $response = array();
        $data = Utility::processedData();
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $customers = $this->sql_model()->queryWithResultSet('
            SELECT customers.*
            FROM customers
            WHERE  
                type = '. $data['customer_type'] .' 
                AND last_name LIKE "%'.$data['term'].'%"
                LIMIT '. $end .', '. $start .'
        ');
        if( count($customers['info']['rows']) ){
            foreach($customers['info']['rows'] as $cs){
                $response[] = array(
                    'id' => implode('|', $cs),
                    'name'=>$cs['last_name'], 
                    'total_count'=> count($customers['info']['rows']) 
                ); 
            }
        }
        _json_echo('getClinicsOptionData', $response);
    }

    // API for App
    
    function getCustomersById(){
        $response = array('status' => 'ERROR', 'message' => 'getCustomersById', 'data'=>array());
        $data = Utility::processedData(true);
        $customers = $this->sql_model()->queryWithResultSet('
            SELECT customers.*
            FROM customers
            WHERE id IN ('."'".''.implode("','", $data["customers"]).''. "'" .')
        ');
        if( $customers['status'] == "OK" ){
            $response["status"] = "OK";
            foreach( $customers["info"]["rows"] as $rs ){
                $rs['affiliate_confirm'] = is_array(json_decode($rs['affiliate_confirm'], true)) ? json_decode($rs['affiliate_confirm'], true) : array();
                $rs['affiliate_cancel'] = is_array(json_decode($rs['affiliate_cancel'], true)) ? json_decode($rs['affiliate_cancel'], true) : array();;
                $response["data"][] = $rs;
            }
        }
        _json_echo('getCustomersById', $response);
    }

    function processParentId(){
        $response = array('status' => 'ERROR', 'message' => 'processParentId', 'data'=>array());
        $data = Utility::processedData(true);
        $customer = $this->sql_model()->queryWithOneResultSet('SELECT* FROM customers WHERE id = "'. $data["id"] .'"');
        $parent_id = is_array(json_decode($customer['parent_id'], true)) ? json_decode($customer['parent_id'], true) : array();
        foreach( $parent_id as $rs ){
            array_splice($data["parentId"], array_search($rs, $data["parentId"]), 1);
        }
        $dataSave = array();
        $skip = array();
        $execution = "OK";
        foreach( $data["parentId"] as $rs ){
            if( $execution == "ERROR" ){
                continue;
            }else{
                $parent = $this->sql_model()->queryWithOneResultSet('SELECT* FROM customers WHERE id = "'. $rs .'"');
                $dataSave['affiliate_confirm'] = is_array(json_decode($parent['affiliate_confirm'], true)) ? json_decode($parent['affiliate_confirm'], true) : array();
                if( !(array_search($rs, $dataSave['affiliate_confirm']) > -1) ){
                    $dataSave['affiliate_confirm'][] = $data['id'];
                    $dataSave['id'] = $rs;
                    $dataSave['affiliate_confirm'] = json_encode($dataSave['affiliate_confirm'], true);
                    $result = Utility::processedSaveData('customers', $dataSave, $skip);
                    if( $result["status"] != "OK" ){
                        $execution = "ERROR";
                    }
                }
            }
        }
        // array_splice($affiliate_cancel, array_search($id_children, $affiliate_cancel), 1);
        // $customer = array();
        // $skip = array();
        // $customer["id"] = $data["id"];
        // $customer["parent_id"] = json_encode($data["parentId"]);
        // $result = Utility::processedSaveData('customers', $customer, $skip);
        if( $execution == "OK" ){
            $response["status"] = "OK";
        }
        _json_echo('processParentId', $response);
    }
    
    function __destruct() {
    }
}
