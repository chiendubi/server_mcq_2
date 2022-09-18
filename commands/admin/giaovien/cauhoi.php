<?php

class cauhoi{
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
        $table = 'c_cauhoi';
        $query = '
            SELECT 
                c_cauhoi.*,
                c_monhoc.*, 
                c_baihoc.*, 
                c_hopde.* 
            FROM c_cauhoi 
            LEFT JOIN c_monhoc ON c_monhoc.code = c_cauhoi.c_monhoc_code 
            LEFT JOIN c_baihoc ON c_baihoc.code = c_cauhoi.c_baihoc_code 
            LEFT JOIN c_hopde ON c_hopde.code = c_cauhoi.c_hopde_code 
            WHERE c_cauhoi.c_monhoc_code = "'.$postData['c_monhoc_code'].'" 
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
         logError ('query-result:'.print_r($query,true));
         logError ('getDataList-result:'.print_r($result,true));

        $adata = array();
        foreach($result['data'] as $rs){
            $rs['C_cauhoi']['dapandung'] =json_decode($rs['C_cauhoi']['dapandung'], true);
            $adata[] = array(
                'DT_RowId' => 'row_'.$rs['C_cauhoi']['id'],
                'c_cauhoi' => $rs['C_cauhoi'],
                'c_monhoc' => $rs['C_monhoc'],
                'c_baihoc' => $rs['C_baihoc'],
                'c_hopde' => $rs['C_hopde']
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
        $data = Utility::processedData(true);

        // logError ('data:'. print_r($data,true));

        $today = date('Y-m-d H:i:s'); # 2022-06-30 12:10:53
        $today_string = date( 'd/m/Y (H:i)', strtotime( $today )); # 30/06/2022 (12:10)

        $skip = array();
        $json = array();
        $json[] = 'dapandung';
        $data['default']['dapandung'] = "'".json_encode($data['default']['dapandung'])."'";
        ## using skip for update not using insert
        if($data['default']['id'] > 0){
            $skip[] = 'code';
        }else{
            $temp_name = substr($data['default']['noidung'], 30);
            $data['default']['code'] = Utility::processedCheckField('c_cauhoi', 'code', $temp_name, true);
        }
        $result = Utility::processedSaveData('c_cauhoi', $data['default'], $skip, $json);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
            
        } 
        
        _json_echo('saveFormData', $response);
    }
    function checkNameUsed($data){
        $sql_model = new VanillaModel();
        $is_exist = 0;
        $condition = $data['id'] > 0 ? ' AND c_cauhoi.id != '.$data['id'].'' : '';
        $name_exist =  $sql_model->queryWithResultSet('
            SELECT c_cauhoi.id  
            FROM c_cauhoi   
            WHERE UPPER(c_cauhoi.name) = UPPER("'.$data['name'].'") AND c_cauhoi.c_namhoc_code = "'.$data['c_namhoc_code'].'" AND c_cauhoi.c_khoi_code = "'.$data['c_khoi_code'].'" '. $condition
        );
        if(count($name_exist['info']['rows']) > 0){
            $is_exist = 1;
        }
        return $is_exist;
    }
    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_cauhoi', $data);
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
  
    function getOptionData(){
        $response = array('status' => 'OK', 'message' => 'getOptionData', 'data' => array());
        $response['data'] = array(
            // 'namhocList' => Utility::getOptionDynamic('global', 'namhoc', 'namhocGlobal', 'list'),
            'monhocList' => Utility::getOptionDynamic('global', 'monhoc', 'monhocGlobal', 'list'),
            'baihocList' => Utility::getOptionDynamic('global', 'baihoc', 'baihocGlobal', 'list'),
            // 'goiList' => Utility::getOptionDynamic('global', 'goi', 'goiGlobal', 'list'),
            'hopdeList' => Utility::getOptionDynamic('global', 'hopde', 'hopdeGlobal', 'list'),
            // 'loaicauhoiList' => Utility::getOptionDynamic('global', 'loaicauhoi', 'loaicauhoiGlobal', 'list'),
        );
        _json_echo('getOptionData', $response);
    }
    function __destruct() {
    }
}
