<?php
class monhocGlobal {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'list':
                $this->list();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('userGlobal', $response);
        }
    }
    function list(){
        $response = array('status' => 'ERROR', 'message' => 'list', 'data' => array());
        $data = Utility::processedData();
        $sql = '
            SELECT c_monhoc.name, c_monhoc.code 
            FROM c_monhoc
        ';
        $result =  $this->sql_model()->queryWithResultSet($sql);
        if($result['status'] = 'OK'){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['name']
                );
            }
        }else{
            $response = array(
                'status' => 'ERROR', 
                'message' => "SUCCESSFUL_NOTIFICATION_RESULTS"
            );
        }
        _json_echo('list', $response);
    }
    function __destruct() {
    }
}
