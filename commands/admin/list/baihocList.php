<?php

class baihocList{
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
         
            case 'getOptionData':
                $this->getOptionData();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('list', $response);
        }
    }
    
    function getDataList(){
        $response = array('status' => 'ERROR', 'message' => 'getDataList', 'data' => array());
        $postData = Utility::processedData();
        $table = 'c_baihoc';
        $query = '
            SELECT 
                c_baihoc.*, 
                c_monhoc.*  
            FROM c_baihoc 
            LEFT JOIN c_monhoc ON (c_monhoc.code = c_baihoc.c_monhoc_code) 
            WHERE 1 
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
         logError ('getDataList-result:'.print_r($result,true));

        $adata = array();
        foreach($result['data'] as $rs){
            $adata[] = array(
                'DT_RowId' => 'row_'.$rs['C_baihoc']['id'],
                'c_baihoc' => $rs['C_baihoc'],
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

        $skip = array();
        $json = array();
        ## using skip for update not using insert
        if($data['default']['id'] > 0){
            $skip[] = 'code';
        }else{
            $data['default']['code'] = Utility::processedCheckField('c_baihoc', 'code', $data['default']['name'], true);
        }

        ## Kiểm tra tên trùng lặp
        $name_exist = 0;
        if($data['default']['id']== 0){
            $name_exist =  $this->checkNameUsed($data['default']);
        }
        if($name_exist == 1){
            $response['status'] = 'ERR_EXIST';
        }else{
            $result = Utility::processedSaveData('c_baihoc', $data['default'], $skip, $json);
            if($result['status'] == "OK"){
                $response['status'] = 'OK';
                
            } 
        } 
        _json_echo('saveFormData', $response);
    }
    function checkNameUsed($data){
        $sql_model = new VanillaModel();
        $is_exist = 0;
        $condition = $data['id'] > 0 ? ' AND c_baihoc.id != '.$data['id'].'' : '';
        $name_exist =  $sql_model->queryWithResultSet('
            SELECT c_baihoc.id  
            FROM c_baihoc   
            WHERE UPPER(c_baihoc.name) = UPPER("'.$data['name'].'") AND c_baihoc.c_monhoc_code = "'.$data['c_monhoc_code'].'" '. $condition
        );
        if(count($name_exist['info']['rows']) > 0){
            $is_exist = 1;
        }
        return $is_exist;
    }
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_baihoc', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        return $response;
    }

    function checkDataUsed(){
        $response = array( 'status' => 'NO', 'message'=> 'checkDataUsed', 'data' => array());
        $data = Utility::processedData();
        $is_check = true;
        if($is_check == true){
            $response['status'] = 'NO';
            $result = $this->sql_model()->queryWithResultSet('
                SELECT c_hopde.id 
                FROM c_hopde 
                WHERE c_hopde.c_baihoc_code = "'. $data['code'] .'"
            ');
            if($result['status'] == "OK"){
                if( count($result['info']['rows']) > 0 ){
                    $response['status'] = 'YES';
                }
            }
        }else{
            $response['status'] = 'NO';
        }
        if($response['status'] == 'NO'){
            $this -> deleteFormData();
        }
        _json_echo('checkDataUsed', $response);
    }

    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $response['data'] = array(
            'namhocList' => Utility::getOptionDynamic('global', 'namhoc', 'namhocGlobal', 'list'),
            'monhocList' => Utility::getOptionDynamic('global', 'monhoc', 'monhocGlobal', 'list'),
        );
        _json_echo('getOptionData', $response);
    }
  
    function __destruct() {
    }
}
