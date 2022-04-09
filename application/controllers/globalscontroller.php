<?php

header('Access-Control-Allow-Origin: *');

class GlobalModel extends VanillaModel {
}

class GlobalsController extends VanillaController {  
    public $global;
    function __construct() {
        $route = ROUTE;
        $controller = CONTROLLER;
        require_once(SERVER_ROOT . '/commands/global/'.$route.'/'.$controller.'.php');
        $this->global = new $controller();
        $this->global->beforeAction(ACTION);
    }
	function beforeAction () {
    }
    function afterAction() {
	}
}
