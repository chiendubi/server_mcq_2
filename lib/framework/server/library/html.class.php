<?php

class HTML {
	private $js = array();

	function shortenUrls($data) {
		$data = preg_replace_callback('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', array(get_class($this), '_fetchTinyUrl'), $data);
		return $data;
	}

	private function _fetchTinyUrl($url) { 
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url[0]); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch); 
		curl_close($ch); 
		return '<a href="'.$data.'" target = "_blank" >'.$data.'</a>'; 
	}

	static function sanitize($data) {
		return mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $data);
	}

	static function link($text,$path,$prompt = null,$confirmMessage = "Are you sure?") {
		$path = str_replace(' ','-',$path);
		if ($prompt) {
			$data = '<a href="javascript:void(0);" onclick="javascript:jumpTo(\''.SERVER_PATH.'/'.$path.'\',\''.$confirmMessage.'\')">'.$text.'</a>';
		} else {
			$data = '<a href="'.SERVER_PATH.'/'.$path.'">'.$text.'</a>';	
		}
		return $data;
	}

	static function includeJs($fileName) {
		$data = '<script src="'.SERVER_PATH.'/js/'.$fileName.'.js"></script>';
		return $data;
	}

	static function includeCss($fileName) {
		$data = '<style href="'.SERVER_PATH.'/css/'.$fileName.'.css"></script>';
		return $data;
	}
    
    static function getJsVars() {
        $data = '
            <script type="text/javascript">
                var APP_PATH = "'. APP_PATH .'";
                var SERVER_PATH = "'. SERVER_PATH .'";
                var CLIENT_PATH = "'. CLIENT_PATH .'";
            </script>';
        return $data;
    }
}
?>