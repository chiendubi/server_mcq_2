<?php

class dashboard{
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
        
            case 'getInfo':
                $this->getInfo();
                break; 

            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('order', $response);
        }
    }

    function getInfo(){
        $response = array('status' => 'OK', 'message' => 'getInfo', 'data' => array());
        _json_echo('saveFormData', $response);
    }

    function __destruct() {
    }
}

