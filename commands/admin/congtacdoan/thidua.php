<?php

class thidua{
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
        $table = 'thidua';
        $query = '
            SELECT 
                thidua.*, c_nhomthidua.name, c_thidua.name 
            FROM thidua 
            LEFT JOIN c_thidua ON c_thidua.code = thidua.c_thidua_code
            LEFT JOIN c_nhomthidua ON c_nhomthidua.code = thidua.c_nhomthidua_code
            WHERE 1
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
        logError('result-thidua:'.print_r($result,true));
        $adata = array();
        foreach($result['data'] as $rs){
            $adata[] =  array(
                'DT_RowId' => 'row_'.$rs['Thidua']['id'],
                'thidua' => $rs['Thidua'],
                'c_nhomthidua' => $rs['C_nhomthidua'],
                'c_thidua' => $rs['C_thidua']
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
        $data['cdate'] = $cdate;
        ## using skip for update not using insert
        if($data['id'] > 0){
            $skip[] = 'cdate';
        }
        $result = Utility::processedSaveData('thidua', $data, $skip);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('saveFormData', $response);
    }

    function deleteFormData($data){
        // $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        // $data = Utility::processedData();
        // $result = Utility::processedDeleteData('thidua', $data);
        // if($result['status'] == "OK"){
        //     $response['status'] = 'OK';
        // } 
        // _json_echo('deleteFormData', $response);
        $result = Utility::processedDeleteData('thidua', $data);
    }
    function checkDataUsed(){
        $response = array( 'status' => 'ERROR', 'message'=> 'checkDataUsed', 'data' => array());
        $data = Utility::processedData();
        logError('checkDataUsed:'.print_r($data, true));
        // $result = $this->sql_model()->queryWithResultSet('
        // ');
        // if($result['status'] == "OK"){
        //     if( count($result['info']['rows']) > 0 ){
        //         $response['status'] = 'OK';
        //     }else{
        //         $this -> deleteFormData();
        //     }
        // }
        $response['status'] = 'NO';
        $this -> deleteFormData($data);
        _json_echo('checkDataUsed', $response);
        }
    ## Get option data  
    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $response['data'] = array(
            'nhomthiduaList' =>  Utility::getOptionDynamic('global', 'thidua', 'thiduaGlobal', 'getNhomThiduaList'),
            'thiduaList' =>  Utility::getOptionDynamic('global', 'thidua', 'thiduaGlobal', 'getThiduaList'),
            'lopList' =>  Utility::getOptionDynamic('global', 'lop', 'lopGlobal', 'getLop')
        );
        _json_echo('getOptionData', $response);
    }
    function __destruct() {
    }
}

