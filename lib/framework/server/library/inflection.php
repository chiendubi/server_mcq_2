<?php

/** Irregular Words

$irregularWords = array(
	'singular' => 'plural' 
);
 
**/

$irregularWords = array(

);

function _json_echo($msg, $val) {
	$json = json_encode($val);
	if (!PRODUCTION_ENVIRONMENT) {
		if (DEVELOPMENT_ENVIRONMENT)
			logError('inflection - _json_echo - msg = ' . $msg);
		if (DEBUG_ENVIRONMENT)
			logError('inflection - _json_echo - value = ' . print_r($val, true));
	}
	echo $json;
}

function _json_convert($str) {
	// change special character to JSON acceptable
	$str = str_replace("\n", '\\n', $str);
	$str = str_replace("\r", '\\r', $str);
	$str = str_replace("\t", '\\t', $str);
	return $str;
}

function _json_encode($val)
{
	if (is_string($val)) return '"'.addslashes($val).'"';
	if (is_numeric($val)) return $val;
	if ($val === null) return 'null';
	if ($val === true) return 'true';
	if ($val === false) return 'false';

	$assoc = false;
	$i = 0;
	foreach ($val as $k=>$v){
		if ($k !== $i++){
			$assoc = true;
			break;
		}
	}
	$res = array();
	foreach ($val as $k=>$v){
		$v = _json_encode($v);
		if ($assoc){
			$k = '"'.addslashes($k).'"';
			$v = $k.':'.$v;
		}
		$res[] = $v;
	}
	$res = implode(',', $res);
	return ($assoc)? '{'.$res.'}' : '['.$res.']';
}
?>




