<?php

class danhgiaTT20{
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getDataList':
                $this->getDataList();
                break;
            case 'loadTeacherList':
                $this->loadTeacherList();
                break;
            case 'loadDanhGiaList':
                $this->loadDanhGiaList();
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
    function loadTeacherList(){
        $response = array('status' => 'ERROR', 'message' => 'loadTeacherList', 'data' => array());
        $data = Utility::processedData();
        
        $result = $this->sql_model()->queryWithResultSet('
        SELECT employees.code as IdTeacher, employees.last_name as NameTeacher, employees.TeacherTo 
        FROM employees 
        WHERE employees.TeacherTo = "'.$data['TeacherTo'].'"
        ');
        // // logError('loadTeacherList:'. print_r($result, true));

        if($result['status'] == "OK"){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){
                $response['data'][] = $dt;
            }
        } 
        _json_echo('loadTeacherList', $response);
    }
    function loadDanhGiaList(){
        $response = array('status' => 'ERROR', 'message' => 'loadDanhGiaList', 'data' => array());
        $data = Utility::processedData();

        $result = $this->sql_model()->queryWithResultSet('
            SELECT 
            employees.last_name as NameTeacher,
            danhgiaTT20GV.id,
            danhgiaTT20GV.IdTeacherdanhgia, 
            danhgiaTT20GV.IdTeacherduocdanhgia, 
            danhgiaTT20GV.tc1,
            danhgiaTT20GV.tc2,
            danhgiaTT20GV.tc3,
            danhgiaTT20GV.tc4,
            danhgiaTT20GV.tc5,
            danhgiaTT20GV.tc5,
            danhgiaTT20GV.tc7,
            danhgiaTT20GV.tc8,
            danhgiaTT20GV.tc9,
            danhgiaTT20GV.tc10,
            danhgiaTT20GV.tc11,
            danhgiaTT20GV.tc12, 
            danhgiaTT20GV.tc3,
            danhgiaTT20GV.tc4,
            danhgiaTT20GV.tc15  
            FROM employees 
            LEFT JOIN danhgiaTT20GV ON (employees.code = danhgiaTT20GV.IdTeacherduocdanhgia) 
            WHERE danhgiaTT20GV.IdTeacherdanhgia = "'.$data['id'].'"
        ');
        // logError('loadDanhGiaList:'. print_r($result, true));

        if($result['status'] == "OK"){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){
                $dt['id'] = $dt['id'] == null ? 0: $dt['id'];
                $response['data'][] = $dt;
            }
        } 
        _json_echo('loadTeacherList', $response);
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
        $number_of_save = 0;
        foreach ($data as $key => $value) {
            // logError('saveFormData:'. print_r($value, true));
            //   
            $skip = array();
            ## using skip for update not using insert
            if($value['id'] > 0){
                $skip[] ='IdTeacher';
                $skip[] ='IdTeacherdanhgia';
                $skip[] ='NameTeacher';
            }
            $result = Utility::processedSaveData('danhgiaTT20GV', $value, $skip);
            if($result['status'] == "OK"){
                $number_of_save ++;
            } 
        }
        if($number_of_save == count($data)){
            $response['status'] = 'OK';
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

