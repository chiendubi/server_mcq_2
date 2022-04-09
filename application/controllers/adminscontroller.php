<?php

header('Access-Control-Allow-Origin: *');

// ---------------------------------------------------------

class AdminModel extends VanillaModel {
}

class AdminsController extends VanillaController {  
    public $admin;
    function __construct() {
        $route = ROUTE;
        $controller = CONTROLLER;
        require_once (SERVER_ROOT . '/application/models/sql_model.php');
        require_once (SERVER_ROOT . '/commands/admin/'.$route.'/'.$controller.'.php');
        $this->admin = new $controller();
        $this->admin->beforeAction(ACTION);
    }
	function beforeAction () {
    }
    function afterAction() {
	}
}
