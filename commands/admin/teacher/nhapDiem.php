<?php

class nhapDiem{
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
    ## Get material list
    function getDataList(){
        $response = array('status' => 'ERROR', 'message' => 'getDataList', 'data' => array());
        $postData = Utility::processedData();
        $table = 'c_khoi';
        $query = '
            SELECT 
                c_khoi.* 
            FROM c_khoi
            WHERE 1
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
        $adata = array();
        foreach($result['data'] as $rs){
            $adata[] =  array(
                'DT_RowId' => 'row_'.$rs['C_khoi']['id'],
                'c_khoi' => $rs['C_khoi']
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
            $ten =  $data['default']['ten'];
            $data['default']['code'] = Utility::processedCheckField('c_khoi', 'code', $ten, true);
        }
        $data['default']['ten'] = Utility::firstCharString($data['default']['ten']);
        $name_exist = $this->checkNameUsed($data);
        if( $name_exist == 1 ){
            $response['status'] = 'ERR_EXIST';
        }else{
            $result = Utility::processedSaveData('c_khoi', $data['default'], $skip);
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
            FROM c_khoi 
            WHERE UPPER(c_khoi.ten) = UPPER("'.$default['ten'].'")
        ');
        if(count($code_exist['info']['rows']) > 0){
            $is_exist = 1;
        }
        return $is_exist;
    }

    ## Delete material        
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_khoi', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('deleteFormData', $response);
    }
    ## Get option data  
    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $response['data'] = array(
            'namhocList' =>  Utility::getOptionDynamic('global','namhoc', 'namhocGlobal', 'getNamhoc' ),
            'hockiList' =>  Utility::getOptionDynamic('global','hocki', 'hockiGlobal', 'getHocki' ),
            'lopList' =>  Utility::getOptionDynamic('global','lop', 'lopGlobal', 'getLop' ),
            'loaidiemList' =>  Utility::getOptionDynamic('global','loaidiem', 'loaidiemGlobal', 'getLoaidiem' )
        );
        _json_echo('getOptionData', $response);
    }
   
    ## Checking material group whether it is used by other place
    function checkDataUsed(){
        $response = array( 'status' => 'ERROR', 'message'=> 'checkDataUsed', 'data' => array());
        $data = Utility::processedData();
        $this -> deleteFormData();

        // $result = $this->sql_model()->queryWithResultSet('
        //     SELECT c_khoi.id 
        //     FROM c_khoi 
        //     WHERE c_khoi.code = "'. $data['code'] .'"
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

