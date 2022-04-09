<?php

class permissionTemplate {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getUsersInformation':
                $this->getUsersInformation();
                break;
            case 'savePermission':
                $this->savePermission();
                break;
            case 'saveUserInformation':
                $this->saveUserInformation();
                break;
            case 'deleteFormData':
                $this->deleteFormData();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'please set action ' .$action);
                _json_echo('permissionTemplate', $response);
        }
    }

    function getUsersInformation(){
        $response = array('status' => 'ERROR', 'message' => 'getUserInformation', 'data' => array());
        $postData = Utility::processedData();
        $table = 'c_permission_template';
        $query = '
            SELECT 
                c_permission_template.*
            FROM c_permission_template
        ';
        $condition = '';
        $result = Utility::processedQueryDataList($table, $query, $condition);
        $adata = array();
        foreach($result['data'] as $rs){
            $rs['C_permission_template']['permissions'] = json_decode($rs['C_permission_template']['permissions'], true);
            $adata[] =  array(
                'DT_RowId' =>'row_'.$rs['C_permission_template']['id'],
                'c_permission_template' => $rs['C_permission_template']
            );
        }
        $response = array(
            "draw" => intval($result['draw']),
            "data" => $adata,
            "options" => array(),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['totalRecordwithFilter']
        );
        _json_echo('getUserInformation', $response);
    }

    function saveUserInformation(){
        $response = array('status' => 'ERROR', 'message' => 'saveUserInformation', 'data' => array());
        $data = Utility::processedData();
        $skip = array();
        $data['name'] = Utility::firstCharString($data['name']);
        if( $data['id'] > 0 ){
            $skip = array('permissions');
            $result = Utility::processedSaveData('c_permission_template', $data, $skip);
            if( $result['status'] == 'OK'){
                $response['status'] = 'OK';
            }
        }else{
            $check_permission = $this->sql_model()->queryWithResultSet('
                SELECT c_permission_template.*
                FROM c_permission_template
                WHERE code = "'.$data['code'].'" 
                    OR name = "'.$data['name'].'"
            ');
            if( !count($check_permission['info']['rows']) > 0 ){
                $result = Utility::processedSaveData('c_permission_template', $data, $skip);
                if( $result['status'] == 'OK'){
                    $response['status'] = 'OK';
                }
            }else{
                $response['status'] = 'PERMISSION_EXIST';
            }
        }
        _json_echo('saveUserInformation', $response);
    }

    function deleteFormData(){
        $response = array('status' => 'ERROR', 'message' => 'deleteFormData', 'data' => array());
        $data = Utility::processedData();
        $result = Utility::processedDeleteData('c_permission_template', $data);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('deleteFormData', $response);
    }

    function savePermission(){
        $response = array('status' => 'ERROR', 'message' => 'savePermission', 'data' => array());
        $data = Utility::processedData();
        $str_permissions = '';
        $skip = array();
        $c_permission_template['id'] = $data['id'];
        $c_permission_template['permissions'] = json_encode($data['permissions'], true);
        $result = Utility::processedSaveData('c_permission_template', $c_permission_template, $skip);
        if($result['status'] == "OK"){
            $response['status'] = 'OK';
        } 
        _json_echo('savePermission', $response);
    } 

    function __destruct() {
    }
}
