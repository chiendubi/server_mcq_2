<?php

class monhocList{
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getDataList':
                $this->getDataList();
                break;
            case 'saveFormData':
                $this->saveFormData();
                break;
            case 'deleteFormData':
                $this->deleteFormData();
                break;
            case 'checkDataUsed':
                $this->checkDataUsed();
                break;
            case 'getData':
                $this->getData();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('list', $response);
        }
    }
    
    function getDataList(){
        $response = array('status' => 'ERROR', 'message' => 'getDataList', 'data' => array());
        $postData = Utility::processedData();
        $table = 'c_monhoc';
        $query = '
            SELECT 
                c_monhoc.* 
            FROM c_monhoc 
            WHERE 1 
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
         logError ('getDataList-result:'.print_r($result,true));

        $adata = array();
        foreach($result['data'] as $rs){
            $adata[] = array(
                'DT_RowId' => 'row_'.$rs['C_monhoc']['id'],
                'c_monhoc' => $rs['C_monhoc']
            );
        }
        $response = array(
            "draw" => intval($result['draw']),
            "data" => $adata,
            "options" => array(),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['totalRecordwithFilter']
        );
        _json_echo('getDataList', $response);
    }
    function saveFormData(){
        $response = array('status' => 'ERROR', 'message' => 'saveFormData', 'data' => array());
        $data_param = Utility::processedData();
        $data = $data_param;

        $today = date('Y-m-d H:i:s'); # 2022-06-30 12:10:53
        $today_string = date( 'd/m/Y (H:i)', strtotime( $today )); # 30/06/2022 (12:10)

         // logError ('data:'.print_r($data,true));
        //  // logError ('today:'.print_r($today,true));
        //  // logError ('today_string:'.print_r($today_string, true));
        // die();
        $skip = array();
        $json = array();
        ## using skip for update not using insert
        if($data['default']['id'] > 0){
            $skip[] = 'code';
        }else{
            $data['default']['code'] = Utility::processedCheckField('c_monhoc', 'code', $data['default']['name'], true);
        }

        ## Kiểm tra tên trùng lặp
        $name_exist = 0;
        if($data['default']['id']== 0){
            $name_exist =  $this->checkNameUsed($data['default']);
        }
        if($name_exist == 1){
            $response['status'] = 'ERR_EXIST';
        }else{
            $result = Utility::processedSaveData('c_monhoc', $data['default'], $skip, $json);
            if($result['status'] == "OK"){
                $response['status'] = 'OK';
                
            } 
        } 
        _json_echo('saveFormData', $response);
    }
    function checkNameUsed($data){
        $sql_model = new VanillaModel();
        $is_exist = 0;
        $condition = $data['id'] > 0 ? 'AND id != '.$data['id'].'' : '';
        $name_exist =  $sql_model->queryWithResultSet('
            SELECT id  
            FROM c_monhoc   
            WHERE UPPER(name) = UPPER("'.$data['name'].'") '. $condition
        );
        if(count($name_exist['info']['rows']) > 0){
            $is_exist = 1;
        }
        return $is_exist;
    }
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_monhoc', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        return $response;
    }

    function checkDataUsed(){
        $response = array( 'status' => 'NO', 'message'=> 'checkDataUsed', 'data' => array());
        $data = Utility::processedData();
        //  // logError ('data:'.print_r($data,true));
        $is_check = false;
        if($is_check == true){
            // $response['status'] = 'NO';
            // $result = $this->sql_model()->queryWithResultSet('
            //     SELECT c_systems_implant.id 
            //     FROM c_systems_implant 
            //     WHERE c_systems_implant.c_producers_code = "'. $data['code'] .'"
            // ');
            // if($result['status'] == "OK"){
            //     if( count($result['info']['rows']) > 0 ){
            //         $response['status'] = 'YES';
            //     }
            // }
        }else{
            $response['status'] = 'NO';
        }
        if($response['status'] == 'NO'){
            $this -> deleteFormData();
        }
        _json_echo('checkDataUsed', $response);
    }

     function getData(){
        $response = array('status' => 'ERROR', 'message' => 'getData', 'data' => array());
        $data = Utility::processedData();

        // $result = $this->sql_model()->queryWithResultSet('
        //     SELECT c_tasks_translation.* 
        //     FROM c_tasks_translation
        //     WHERE c_task_code = "'.$data['c_task_code'].'"
        // ');
        // if($result['status'] == "OK"){
        //     $response['status'] = 'OK';
        //     $response['data'] = $result['info']['rows'];
        // } 
        _json_echo('getData', $response);
    }
  
    function __destruct() {
    }
}
