<?php
class Cache {

	function get($fileName) {
		$fileName = SERVER_ROOT.DS.'tmp'.DS.'cache'.DS.$fileName;
		// logError('GET:'.print_r($fileName, true));
		if (file_exists($fileName)) {
			$handle = fopen($fileName, 'rb');
			$variable = fread($handle, filesize($fileName));
			fclose($handle);
			return unserialize($variable);
		} else {
			return null;
		}
	}
	
	function set($fileName,$variable) {
		$fileName = SERVER_ROOT.DS.'tmp'.DS.'cache'.DS.$fileName;
		// logError('SET:'.print_r($fileName, true));
		$handle = fopen($fileName, 'a');
		fwrite($handle, serialize($variable));
		fclose($handle);
	}

}
?>
