<?php
class bghGlobal{
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getDanhMucThiDuaList':
                $this->getDanhMucThiDuaList();
                break;
            case 'getThiduaList':
                $this->getThiduaList();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Error');
                _json_echo('thiduaGlobal', $response);
        }
    }
    function getDanhMucThiDuaList(){
        $response = array('status' => 'ERROR', 'message' => 'getDanhMucThiDuaList', 'data' => array());
        $result =  $this->sql_model()->queryWithResultSet('
            SELECT c_danhmucdanhgia_bgh.*  
            FROM c_danhmucdanhgia_bgh
        ');
        if($result['status'] == 'OK'){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['name']);
            }
        }
        _json_echo('getDanhMucThiDuaList', $response);
    }
    function getThiduaList(){
        $response = array('status' => 'ERROR', 'message' => 'getThiduaList', 'data' => array());
        $result =  $this->sql_model()->queryWithResultSet('
            SELECT c_thidua.* 
            FROM c_thidua
            WHERE 1;
        ');
        if($result['status'] == 'OK'){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['name'],
                    'mark' => $dt['mark'],
                    'c_nhomthidua_code' => $dt['c_nhomthidua_code']
                );
            }
        }
        _json_echo('getThiduaList', $response);
    }
    function __destruct() {
    }
}
