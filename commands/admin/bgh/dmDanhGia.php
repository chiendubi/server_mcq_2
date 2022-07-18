<?php

class dmDanhGia{
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
            case 'getOptionData':
                $this->getOptionData();
                break;
            case 'checkDataUsed':
                $this->checkDataUsed();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('listLabo', $response);
        }
    }

    function getDataList(){
        $response = array('status' => 'ERROR', 'message' => 'getDataList', 'data' => array());
        $postData = Utility::processedData();
        $table = 'c_danhmucdanhgia_bgh';
        $query = '
            SELECT 
                c_danhmucdanhgia_bgh.* 
            FROM c_danhmucdanhgia_bgh 
            WHERE 1
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
        logError('result:'.print_r($result,true));
        $adata = array();
        foreach($result['data'] as $rs){
            $adata[] =  array(
                'DT_RowId' => 'row_'.$rs['C_danhmucdanhgia_bgh']['id'],
                'c_danhmucdanhgia_bgh' => $rs['C_danhmucdanhgia_bgh'],
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
    ## Add, Edit
    function saveFormData(){
        $response = array('status' => 'ERROR', 'message' => 'saveFormData', 'data' => array());
        $data = Utility::processedData();
        $cdate = date('Y-m-d H:i:s');
        $skip = array();
        $data['default']['cdate'] = $cdate;
        ## using skip for update not using insert
        if($data['default']['id'] > 0){
            $skip[] ='code';
            $skip[] = 'cdate';
        }else{
            $name =  $data['default']['name'];
            $data['default']['code'] = Utility::processedCheckField('c_thidua', 'code', $name, true);
        }
        $data['default']['name'] = Utility::firstCharString($data['default']['name']);
        $name_exist = $this->checkNameUsed($data);
        if($data['default']['id'] > 0){
            $name_exist = 0;
        }
        if( $name_exist ){
            $response['status'] = 'ERR_EXIST';
        }else{
            $result = Utility::processedSaveData('c_thidua', $data['default'], $skip);
            if($result['status'] == "OK"){
                $response['status'] = 'OK';
            } 
        }
        _json_echo('saveFormData', $response);
    }

    function checkNameUsed( $data ){
        $default = $data['default'];
        $is_exist = 0;
        $condition = '';
        $code_exist = $this->sql_model()->queryWithResultSet('
            SELECT * 
            FROM c_thidua 
            WHERE 
                UPPER(c_thidua.name) = UPPER("'.$default['name'].'") 
        ');
        if(count($code_exist['info']['rows']) > 0){
            $is_exist = 1;
        }
        return $is_exist;
    }

   
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_thidua', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('deleteFormData', $response);
    }
    ## Get option data  
    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $response['data'] = array(
            'nhomthiduaList' =>  Utility::getOptionDynamic('global', 'thidua', 'thiduaGlobal', 'getNhomThiduaList'),
        );
        _json_echo('getOptionData', $response);
    }
   
    
    function checkDataUsed(){
        $response = array( 'status' => 'ERROR', 'message'=> 'checkDataUsed', 'data' => array());
        $data = Utility::processedData();
        $this -> deleteFormData();

        // $result = $this->sql_model()->queryWithResultSet('
        //     SELECT c_thidua.id 
        //     FROM c_thidua 
        //     WHERE c_thidua.code = "'. $data['code'] .'"
        // ');
        // if($result['status'] == "OK"){
        //     if( count($result['info']['rows']) > 0 ){
        //         $response['status'] = 'OK';
        //     }else{
        //         $this -> deleteFormData();
        //     }
        // }
        _json_echo('checkDataUsed', $response);
    }

    function __destruct() {
    }
}

