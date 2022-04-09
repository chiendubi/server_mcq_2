<?php
class signup {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'saveFormData':
                $this->saveFormData();
                break;
            case 'getClinicsOptionData':
                $this->getClinicsOptionData();
                break;
            case 'getOptionData':
                $this->getOptionData();
                break;
            case 'getDataCustomersById':
                $this->getDataCustomersById();
                break;
            case 'searchCustomersForApp':
                $this->searchCustomersForApp();
                break;
            case 'checkCustomers':
                $this->checkCustomers();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action');
                _json_echo('signup', $response);
        }
    }
    function saveFormData(){
        require_once(SERVER_ROOT . '/commands/global/user/userGlobal.php');
        $response = array('status' => 'ERROR', 'message' => 'saveFormData', 'data' => array(), 'error' => array());
        $postData = Utility::processedData(true);
        $other = $postData['other'];
        $parent = array();
        if($other['type'] == 'companys'){
            $parent = $postData['data']['company'];
            $parent['tax_code'] = isset($parent['tax_code']) ? $parent['tax_code'] : null;
        }else if($other['type'] == 'clinics'){
            if( $postData['data']['company']['phone1'] == ''){
                $parent = $postData['data']['clinics'];
                $parent['tax_code'] = isset($parent['tax_code']) ? $parent['tax_code'] : null;
            }else{
                $parent = $postData['data']['company'];
                $children = $postData['data']['clinics'];
                $parent['tax_code'] = isset($parent['tax_code']) ? $parent['tax_code'] : null;
                $children['tax_code'] = isset($children['tax_code']) ? $children['tax_code'] : null;
            }
        }else{
            $parent = $postData['data']['clinics'];
            $children = $postData['data']['personal'];
            $parent['tax_code'] = isset($parent['tax_code']) ? $parent['tax_code'] : null;
            $children['tax_code'] = isset($children['tax_code']) ? $children['tax_code'] : null;
        }
        if(isset($parent)){
            ## Check email and phone exist (user)
            if( $parent['id'] > 0 ){
                $userExistParent['fieldError'] = false;
            }else{
                $userExistParent = userGlobal::processUserCheck('customers', $parent['email'], $parent['country_pcode1'], $parent['phone1'], $parent['id'], $parent['tax_code']);
            }
            if(isset($children)){
                $userExistChildren = userGlobal::processUserCheck('customers', $children['email'], $children['country_pcode1'], $children['phone1'], $children['id'],$children['tax_code']);
            }else{
                $userExistChildren['fieldError'] = false;
            }
            if(!$userExistParent['fieldError'] && !$userExistChildren['fieldError']){
                if( $parent['id'] > 0 ){
                    $result['status'] = "OK";
                    if(isset($children)){
                        // $parent_id[] = strval($parent['id']);
                        // $children['parent_id'] = json_encode($parent_id, true);
                        $children['parent_id'] = json_encode(array(), true);
                        $children['affiliate_confirm'] = json_encode(array(), true);
                        $children['affiliate_cancel'] = json_encode(array(), true);
                        $data_confirm = $this->sql_model()->queryWithOneResultSet('
                            SELECT 
                                customers.affiliate_confirm
                            FROM customers
                            WHERE id = "'.$parent["id"].'"
                        ');
                        $data = array();
                        $skip = array();
                        $affiliate_confirm = json_decode($data_confirm['affiliate_confirm'], true);
                    }
                }else{
                    if( isset($parent['parent_id']) ){
                        $parent['parent_id'] = $parent['parent_id'] == '' ? array() : array(strval($parent['parent_id']));
                    }else{
                        $parent['parent_id'] = array();
                    }
                    
                    $parent['parent_id'] = json_encode($parent['parent_id'], true);
                    $result = $this->processSaveFormData($parent, $other);
                    if($result['status'] == 'OK' && isset($children)){
                        $parent_id[] = strval($result['data']);
                        $children['parent_id'] = json_encode($parent_id, true);
                    }
                }
                if($result['status'] == "OK"){
                    if(isset($children)){
                        $result = $this->processSaveFormData($children, $other);
                        if($result['status'] == "OK"){
                            $response['status'] = "OK";
                            if( $parent['id'] > 0 ){
                                $data['id'] = $parent['id'];
                                $affiliate_confirm[] = strval($result['data']);
                                $data['affiliate_confirm'] = is_array($affiliate_confirm) ? json_encode($affiliate_confirm, true) : json_encode(array(strval($result['data'])), true);
                                $result = Utility::processedSaveData('customers', $data, $skip);
                                if($result['status'] == "OK"){
                                    Utility::getOptionDynamic('global', 'notifycation', 'notifycationGlobal', 'sendNotificationAffiliate');
                                }
                            }
                        }else{
                            $response['status'] = "ERROR";
                            $response['error']['children'] = $result['error'];
                        }
                    }else{
                        $response['status'] = "OK";
                    }
                }else{
                    $response['status'] = "ERROR";
                    $response['error']['parent'] = $result['error'];
                }
            }else{
                $response['status'] = "ERROR";
                $response['error']['parent'] = $userExistParent;
                $response['error']['children'] = $userExistChildren;
            }
        }
        _json_echo('saveFormData', $response);
    }

    function processSaveFormData($item, $other){
        $response = array('status' => 'ERROR', 'message' => 'processSaveFormData', 'data' => array(), 'error' => array());
        $item['cdate'] = date('Y-m-d H:i:s');
        $item['last_name'] = Utility::firstCharString($item['last_name']);
        $item['first_name'] = Utility::firstCharString($item['first_name']);
        ## Check email and phone exist (user)
        $userExist = userGlobal::processUserCheck('customers', $item['email'], $item['country_pcode1'], $item['phone1'], $item['id'], $item['tax_code']);
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

    function getClinicsOptionData(){
        $data = Utility::processedData(true);
        $customer_type= $data['customer_type'];
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $select_string = 'SELECT * FROM customers ';
        $limit=' LIMIT '.$end.','.$start;
        $conditional_string = ' WHERE 1 ';
        $conditional_string .= ' AND type = "'. $customer_type .'" ';
        $conditional_string .= ' AND last_name LIKE "%'.$data['term'].'%" ';
        $sql_string_v2= $select_string.$conditional_string.$limit;
        $customers = $this->sql_model()->queryWithResultSet($sql_string_v2);
        $count = count($customers['info']['rows']);
        $data = array();
        if($count > 0){
            foreach($customers['info']['rows'] as $cs){
                $data[] = array(
                    'id'=>$cs['id'].'|'.$cs['code'].'|'.$cs['address'].'|'.$cs['country_pcode1'].'|'.$cs['phone1'].'|'.$cs['tax_code'].'|'.$cs['email'].'|'.$cs['first_name'].'|'.$cs['last_name'].'|isChoose',
                    'name'=>$cs['first_name'] . ' ' . $cs['last_name'] , 
                    'total_count'=>$count ); 
            }
        }
        _json_echo('getClinicsOptionData', $data);
    }

    ## API FOR APP

    function getDataCustomersById(){
        $response = array('status' => 'OK', 'message' => 'getDataCustomersById', 'data' => array());
        $data = Utility::processedData(true);
        $result = $this->sql_model()->queryWithResultSet('
            SELECT*
            FROM customers
            WHERE id = '. $data['id'] .'
        ');
        if( $result['status'] == "OK" ){
            $response['status'] = 'OK';
            $response['data'] = array(
                'infoCustomer' => $result['info']['rows'][0]
            );
        }
        _json_echo('getDataCustomersById', $response);
    }

    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $data = Utility::processedData(true);
        $response['data'] = array(
            'customers' => $this->getClinics($data['type'])
        );
        foreach( $response['data']['customers'] as $key => $rs ){
            $response['data']['customers'][$key]['affiliate_confirm'] = is_array(json_decode($rs['affiliate_confirm'], true)) ? json_decode($rs['affiliate_confirm'], true) : array();
            $response['data']['customers'][$key]['affiliate_cancel'] = is_array(json_decode($rs['affiliate_cancel'], true)) ? json_decode($rs['affiliate_cancel'], true) : array();
        }
        _json_echo('getOptionData', $response);
    }

    function getClinics( $type ){
        $response = array();
        $cilinics = $this->sql_model()->queryWithResultSet('
            SELECT*
            FROM customers
            WHERE type = '.$type.'
        ');
        if( $cilinics['status'] == 'OK' ){
            if( count($cilinics['info']['rows']) > 0 ){
                $response = $cilinics['info']['rows'];
                // foreach( $cilinics['info']['rows'] as $item ){
                //     $response[] = array(
                //         'label' => $item['last_name'].' '.$item['first_name'],
                //         'value' => $item['id']
                //     );
                // }
            }
        }
        return $response;
    }

    function searchCustomersForApp(){
        $response = array('status' => 'ERROR', 'message' => 'searchCustomersForApp', 'data' => array());
        $data = Utility::processedData(true);
        $page = $data['page'];
        $resultCount = 10;
        $end = ($page - 1) * $resultCount;       
        $start = $end + $resultCount;
        $cilinics = $this->sql_model()->queryWithResultSet('
            SELECT*
            FROM customers
            WHERE
                type = '. $data['type'] .' 
                AND concat( last_name, first_name ) LIKE "%'.$data['customer_type'].'%"
                LIMIT '. $end .', '. $start .'
        ');
        if( $cilinics['status'] == 'OK' ){
            $response['status'] = 'OK';
            if( count($cilinics['info']['rows']) > 0 ){
                $response['data'] = $cilinics['info']['rows'];
            }
        }
        _json_echo('searchCustomersForApp', $response);
    }

    function checkCustomers(){
        $response = array('status' => 'ERROR', 'message' => 'checkCustomers', 'data' => array());
        $data = Utility::processedData(true);
        $email = $data['email'];
        $phone = $data['phone'];
        $country_pcode = $data['country_pcode'];
        $err_phone = 0;
        $err_email = 0;
        if(!empty($email)){
            $email_check = $this->sql_model()->queryWithOneResultSet('SELECT * FROM customers WHERE email = "' . $email . '"');
            if ($email_check) {
                $err_email = 1;
            } else {
                $email_check = $this->sql_model()->queryWithOneResultSet('SELECT * FROM employees WHERE email = "' . $email . '"');
                if($email_check){
                    $err_email = 1;
                }
            }
        }
        if(!empty($phone) && !empty($country_pcode)){
            $phone_check = $this->sql_model()->queryWithOneResultSet('SELECT * FROM customers WHERE country_pcode1="'.$country_pcode.'" AND phone1 = "' . $phone . '"');
            if ($phone_check) {
                $err_phone = 1;
            } else {
                $phone_check = $this->sql_model()->queryWithOneResultSet('SELECT * FROM employees WHERE country_pcode1="'.$country_pcode.'" AND phone1 = "' . $phone . '"');
                if($phone_check){
                    $err_phone = 1;
                }
            }
        }
        $response["data"]["ERR"] = array(
            "phone" => $err_phone,
            "email" => $err_email
        );
        if( $err_phone == 0 && $err_email == 0 ){
            $response["status"] = "OK";
        }
        _json_echo('checkCustomers', $response);
    }


    function __destruct() {
    }
}
