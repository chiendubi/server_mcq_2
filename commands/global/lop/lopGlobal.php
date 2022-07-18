<?php
class lopGlobal {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getLop':
                $this->getLop();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('chainGlobal', $response);
        }
    }
    public function getLop(){
        $response = array('status' => 'ERROR', 'message' => 'getLop', 'data' => array());
        $data = Utility::processedData();
        $code = isset($data['code']) ? $data['code'] : "";
        $sql = 'SELECT c_lop.*, c_khoi.name as khoi_name 
        FROM c_lop 
        LEFT JOIN c_khoi ON c_khoi.code = c_lop.c_khoi_code'
        ;
        $condition = '';
        $condition .= $code != "" ? ' WHERE code = "'.$code.'"' : '';
        $sql .= $condition;
        $list = $this->sql_model()->queryWithResultSet($sql);
        if($list['status'] == 'OK'){
            $response['status'] = 'OK';
            foreach($list['info']['rows'] as $dt){    
                $response['data'][] = array(
                    'value' => $dt['code'], 
                    'label' => $dt['khoi_name'].$dt['name']
                );
            }
        }
        _json_echo('getLop', $response);
    }

    function __destruct() {
    }
}
