<?php

header('Access-Control-Allow-Origin: *');

// ---------------------------------------------------------

class UsermasterModel extends VanillaModel {
}

class UsermastersController extends VanillaController {  
    public $user;
    function __construct() {
        $route = ROUTE;
        $controller = CONTROLLER;
        require_once (SERVER_ROOT . '/application/models/sql_model.php');
        require_once (SERVER_ROOT . '/commands/user/'.$route.'/'.$controller.'.php');
        $this->user = new $controller();
        $this->user->beforeAction(ACTION);
    }
	function beforeAction () {
    }
    function afterAction() {
	}
}
