<?php
class chainGlobal {
    use sqlModel;
	function beforeAction ($action) {
        switch ($action) {
            case 'getModulesChain':
                $this->getModulesChain();
                break;
            case 'getChainByHostName':
                $this->getChainByHostName();
                break;
            default:
                $response = array('status' => 'ERROR', 'message' => 'Please set beforeAction '. $action);
                _json_echo('chainGlobal', $response);
        }
    }
    function getModulesChainV2(){
        $response = array('status' => 'ERROR', 'message' => 'getModulesChain', 'data' => array());
        $data = Utility::processedData();
        $chain_code = $data['chain_code'];
        $chainPermission = connect_core::sqlQuery('SELECT * FROM chain WHERE code = "'.$chain_code.'"');
        ## logError('chainPermission: ' .print_r($chainPermission, true));
        $modulesAppPermission = connect_core::sqlQuery('
            SELECT c_modules.*, c_modules_translation.name 
            FROM c_modules 
            LEFT JOIN c_modules_translation ON ( c_modules_translation.c_module_code = c_modules.code AND c_modules_translation.language_code = "'. LANGUAGE .'" )
        ');
        ## logError('modulesAppPermission: ' .print_r($modulesAppPermission, true));
        $modules = array();
        if(count($chainPermission['info']['rows']) > 0){
            $response['status'] = "OK";
            if(count($modulesAppPermission['info']['rows']) > 0){
                $arr = $modulesAppPermission['info']['rows'];
                if(isset($chainPermission['info']['rows'][0]['app_permission'])){
                    $modules = json_decode($chainPermission['info']['rows'][0]['app_permission'], true);
                    $response['data']['app_permission'] = $this->createTree($arr, $modules);
                }
            }
        }else{
            $response = array(
                'status' => 'ERROR', 
                'message' => SUCCESSFUL_NOTIFICATION_RESULTS
            );
        }
        ## logError('response: ' .print_r($response, true));
        _json_echo('getModulesChain', $response);
    }
    function getModulesChain(){
        $response = array('status' => 'ERROR', 'message' => 'getModulesChain', 'data' => array());
        $data = Utility::processedData();
        $languages = array(
            "value" => "vi",
            "label" => "Tiếng Việt"
        );

        $response['status'] = 'OK';
        $response['data']['active'] = 1;
        $response['data']['code'] = "ESC_LAB.JUSTOPEN";
        $response['data']['languages'] = array();
        $response['data']['languages'][] = $languages;
      
        _json_echo('getModulesChain', $response);
    }

    function getChainByHostName(){
        $response = array('status' => 'ERROR', 'message' => 'getModulesChain', 'data' => array());
        $data = Utility::processedData();
        $host_name = $data['host_name'];
        logError('host_name:'. $host_name);
        $chainPermission = $this->sql_model()->queryWithResultSet('SELECT code, language_permission, active  FROM chain WHERE host ="'.$host_name.'"');
        logError('chainPermission:'. print_r($chainPermission, true));

        if(count($chainPermission['info']['rows']) > 0){
            $response['data'] = array(
                'languages' => array(),
                'active' => $chainPermission['info']['rows'][0]['active'],
                'code' => $chainPermission['info']['rows'][0]['code']
            );
            $languages = json_decode($chainPermission['info']['rows'][0]['language_permission'], true);
            if(count($languages) > 0){
                $condition = '';
                foreach($languages as $s){
                    $condition .= 'OR c_languages.code = "'.$s.'" ';
                }                
                $condition = 'WHERE ' .trim($condition, 'OR');
                $result = $this->sql_model()->queryWithResultSet('
                    SELECT name, code FROM c_languages '. $condition .'
                ');
                if(count($result['info']['rows']) > 0){
                    $response['status'] = 'OK';
                    foreach($result['info']['rows'] as $rs){    
                        $response['data']['languages'][] = array('value' => $rs['code'], 'label' => $rs['name']);
                    }
                }else{
                    $response = array(
                        'status' => 'ERROR', 
                        'message' => 'ERROR'
                    );
                }
            }
        }else{
            $response = array(
                'status' => 'ERROR', 
                'message' => 'ERROR'
            );
        }
        _json_echo('getChainByHostName', $response);
    }
    /* Recursive branch extrusion */
    function createBranch(&$parents, $children, $lv = 0) {
        $tree = array();
        $count = 0;
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = $this->createBranch($parents, $parents[$child['id']], $lv + 1);
            }
            $child['level'] = $lv;
            $tree[] = $child;
        } 
        return $tree;
    }
    /* Initialization */
    function createTree($flat, $modules, $root = 0) {
        $parents = array();
        foreach ($flat as $a) {
            if(array_search($a['code'], $modules) > -1){
                $parents[$a['parent_id']][] = array(
                    'id' => $a['id'],
                    'text' => $a['name'],
                    'code' => $a['code']
                );
            }
        }
        return $this->createBranch($parents, $parents[$root]);
    }
    function checkUsername($username){
    }
    function __destruct() {
    }
}
