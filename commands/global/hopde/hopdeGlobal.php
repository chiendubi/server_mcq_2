<?php
class hopdeGlobal {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'list':
                $this->list();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('hopdeGlobal', $response);
        }
    }
    function list(){
        $response = array('status' => 'ERROR', 'message' => 'list', 'data' => array());
        $data = Utility::processedData();
        
        $sql = '
            SELECT 
            c_hopde.name, 
            c_hopde.code, 
            c_monhoc.code as c_monhoc_code ,
            c_monhoc.name as c_monhoc_name ,
            c_baihoc.code as c_baihoc_code ,
            c_baihoc.name as c_baihoc_name 
            FROM c_hopde
            LEFT JOIN c_monhoc ON (c_monhoc.code = c_hopde.c_monhoc_code)
            LEFT JOIN c_baihoc ON (c_baihoc.code = c_hopde.c_baihoc_code)
        ';
        $result =  $this->sql_model()->queryWithResultSet($sql);
        if($result['status'] = 'OK'){
            $response['status'] = 'OK';
            foreach($result['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['name'],
                    'c_monhoc_code' => $dt['c_monhoc_code'],
                    'c_monhoc_name' => $dt['c_monhoc_name'],
                    'c_baihoc_code' => $dt['c_baihoc_code'],
                    'c_baihoc_name' => $dt['c_baihoc_name']
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
