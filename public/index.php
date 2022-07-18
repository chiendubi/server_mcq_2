<?php
    define('EOL', PHP_EOL);
    define('DS', DIRECTORY_SEPARATOR);
    define('SERVER_ROOT', dirname(__DIR__));
    define('SYSTEM_ROOT', dirname(SERVER_ROOT));
    define('SERVER_PDF_ROOT', SERVER_ROOT . DS . 'pdf');
    define('SERVER_LIB_ROOT', SERVER_ROOT . DS . 'lib');
    define('FRAMEWORK_ROOT', SERVER_ROOT . DS . 'lib/framework');
    define('FRAMEWORK_SERVER_ROOT', FRAMEWORK_ROOT . DS . 'server');
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    $path_url =  explode(":", $_GET['url']);
    $url = isset($path_url[0]) ? $path_url[0] : null;
    $controller = isset($path_url[1]) ? $path_url[1] : null;
    $action = isset($path_url[2]) ? $path_url[2] : null;
    $route = explode("/", $url);
    $route = $route[1] ? $route[1] : null;
    $language = isset($_POST['language']) ? $_POST['language'] : 'vi';
    define('ROUTE', $route);
    define('CONTROLLER', $controller);
    define('ACTION', $action);
    define('LANGUAGE', $language);

    require_once (SERVER_ROOT . '/config/environment.php');
    require_once (SERVER_ROOT . '/config/constant.php');
    // require_once (__DIR__ . DS . 'connect_frontend.php');
    require_once (__DIR__ . DS . 'connect_core.php');
    require_once (__DIR__ . DS . 'utility.php');
    // require_once (__DIR__ . DS . 'notification.php');
    // require_once (__DIR__ . DS . 'sms.php');
    // require_once (__DIR__ . DS . 'email.php');
    require_once (FRAMEWORK_SERVER_ROOT . DS . 'library' . DS . 'bootstrap.php');
?>