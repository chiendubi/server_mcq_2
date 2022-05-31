<?php
/* System */
$url_temp = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
$production_mode = isset($_POST['production_mode']) ? $_POST['production_mode'] : '';
$deploy_to = 'DEV';  // 'TEST PRO DEV
if (strpos($url_temp, 'localhost') || $production_mode == "DEV") {
    
    define ('LOCAL_DATA', true);

    define ('PRODUCTION_ENVIRONMENT',false);
    define ('DEVELOPMENT_ENVIRONMENT',true);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'root_db');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    define('DB_HOST', 'localhost');

    define('DB_NAME_FRONTEND', 'chez_frontend');
    define('DB_USER_FRONTEND', 'root');
    define('DB_PASSWORD_FRONTEND', '');
    define('DB_HOST_FRONTEND', 'localhost');

    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'http://localhost/esmiles/root/');
}else if($deploy_to == 'TEST'){

    define ('LOCAL_DATA', false);
    define ('PRODUCTION_ENVIRONMENT',true);
    define ('DEVELOPMENT_ENVIRONMENT',false);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'lab_demo');
    define('DB_USER', 'chezdentalad');
    define('DB_PASSWORD', '8qih3jBj7lJhR3Po');
    define('DB_HOST', 'localhost');
    
    define('DB_NAME_FRONTEND', 'pvh5ae2c_chez_frontend');
    define('DB_USER_FRONTEND', 'chezdentalad');
    define('DB_PASSWORD_FRONTEND', '8qih3jBj7lJhR3Po');
    define('DB_HOST_FRONTEND', 'localhost');
    
    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'https://justopen.info/');
}else if($deploy_to == 'PRO'){
    define ('LOCAL_DATA', false);

    define ('PRODUCTION_ENVIRONMENT',true);
    define ('DEVELOPMENT_ENVIRONMENT',false);
    define ('DEBUG_ENVIRONMENT',false);
    define ('MAINTENANCE_ENVIRONMENT',false);

    define('DB_NAME', 'ailabo');
    define('DB_USER', 'chezdentalad');
    define('DB_PASSWORD', '8qih3jBj7lJhR3Po');
    define('DB_HOST', 'localhost');
    
    define('DB_NAME_FRONTEND', 'pvh5ae2c_chez_frontend');
    define('DB_USER_FRONTEND', 'chezdentalad');
    define('DB_PASSWORD_FRONTEND', '8qih3jBj7lJhR3Po');
    define('DB_HOST_FRONTEND', 'localhost');
    
    define('DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh');
    define('APP_PATH', 'https://justopen.info/');
}

?>

