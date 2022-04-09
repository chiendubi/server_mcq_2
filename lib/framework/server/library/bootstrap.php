<?php
    ini_set('max_execution_time', 0);	// no limit on execution time
    session_start();
    date_default_timezone_set(DEFAULT_TIMEZONE);

    require_once (FRAMEWORK_SERVER_ROOT . DS . 'library' . DS . 'routing.php');
    require_once (FRAMEWORK_SERVER_ROOT . DS . 'library' . DS . 'inflection.php');
    require_once (FRAMEWORK_SERVER_ROOT . DS . 'library' . DS . 'shared.php');
?>