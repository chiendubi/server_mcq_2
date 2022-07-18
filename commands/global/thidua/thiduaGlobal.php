<?php
class thiduaGlobal{
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getNhomThiduaList':
                $this->getNhomThiduaList();
                break;
            case 'getThiduaList':
                $this->getThiduaList();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Error');
                _json_echo('thiduaGlobal', $response);
        }
    }
    function getNhomThiduaList(){
        $response = array('status' => 'ERROR', 'message' => 'getNhomThiduaList', 'data' => array());
        $result =  $this->sql_model()->queryWithResultSet('
            SELECT c_nhomthidua.*  
            FROM c_nhomthidua
        ');
        if($result['status'] == 'OK'){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['name']);
            }
        }
        _json_echo('getNhomThiduaList', $response);
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
