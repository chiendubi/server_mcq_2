<?php
/* System */
$url_temp = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
$production_mode = isset($_POST['production_mode']) ? $_POST['production_mode'] : '';
$deploy_to = 'TEST';  // 'TEST PRO DEV


if (strpos($url_temp, 'localhost') || $production_mode == "DEV") {
    
    define ('LOCAL_DATA', true);

    define ('PRODUCTION_ENVIRONMENT',false);
    define ('DEVELOPMENT_ENVIRONMENT',true);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'mcq_db');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    define('DB_HOST', 'localhost');

    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'http://localhost/TruongHocSo/mcq_2/');
}else if($deploy_to == 'TEST'){

    define ('LOCAL_DATA', false);
    define ('PRODUCTION_ENVIRONMENT',true);
    define ('DEVELOPMENT_ENVIRONMENT',false);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'mcq_db');
    define('DB_USER', 'cle_1');
    define('DB_PASSWORD', 'temp231');
    define('DB_HOST', 'localhost');
    
    
    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'https://justopen.info/');
}else if($deploy_to == 'PRO'){
    define ('LOCAL_DATA', false);

    define ('PRODUCTION_ENVIRONMENT',true);
    define ('DEVELOPMENT_ENVIRONMENT',false);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'mcq_db');
    define('DB_USER', 'cle_1');
    define('DB_PASSWORD', 'temp231');
    define('DB_HOST', 'localhost');
    
    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'https://justopen.info/');
}

?>

