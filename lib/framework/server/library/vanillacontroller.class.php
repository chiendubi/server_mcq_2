<?php

class VanillaController {
	
	protected $_controller;
	protected $_action;
	protected $_template;

	public $doNotRenderHeader;
	public $render;
    public $params = array();

	function __construct($controller, $action) {
		
		global $inflect;

		$this->_controller = ucfirst($controller);
		$this->_action = $action;
		$model = ucfirst($inflect->singularize($controller));
		
		$this->render = 0;
		$this->doNotRenderHeader = 1;
		
		// set basic parameters
		$this->$model = new $model;
		$this->_template = new Template($controller,$action);
		
		// print controller and action flow
		if ($controller == 'onlineusers' && ($action == 'admin_listOnlineUser' || $action == 'admin_checkOnlineUser')) {
		} else {
			if (!PRODUCTION_ENVIRONMENT) {
				if (DEVELOPMENT_ENVIRONMENT) {
					logError('vanillacontroller.class.php - controller/action?uri: ' . $controller . '/' . $action .	'?' . substr(strchr($_SERVER['REQUEST_URI'], "?"), 1));
				// if (DEBUG_ENVIRONMENT)
					logError('vanillacontroller.class.php - post: ' . _json_encode($_POST));
				}
			}
		}

        // Convert query string into $_GET array if parameters are available
		if(($startPos = strpos($_SERVER['REQUEST_URI'], '?')) !== FALSE) {
            $queryString = substr($_SERVER['REQUEST_URI'], $startPos + 1, strlen($_SERVER['REQUEST_URI']));
            $pairs = explode('&', $queryString);
            foreach($pairs as $pair) {
                list($key, $value) = explode('=', $pair);
                $_GET[$key] = $value;    
            }
        }

	}

	function set($name,$value) {
		$this->_template->set($name,$value);
	}

	function __destruct() {
		if ($this->render) {
			$this->_template->render($this->doNotRenderHeader);
		}
	}
		
}