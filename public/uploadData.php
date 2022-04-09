<?php

// uploadData();

header('Content-type: text/html; charset=utf-8');
define('EOL', '<br>');
define('DS', DIRECTORY_SEPARATOR);
define('SERVER_ROOT', dirname(__DIR__));
define('APP_ROOT', dirname(SERVER_ROOT));
define('SYSTEM_ROOT', dirname(APP_ROOT));

require_once (APP_ROOT . '/share/config/config.php');

$file = $_GET['file'];
$file = APP_ROOT . '/database/' . $file;

$sql_file = mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $file);

// echo "sql_file: " . $sql_file . EOL;
$msg = 'File ' . $sql_file . ' không có!';
if ($sql_file)
	$msg = importData(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME, $sql_file); 

echo $msg . EOL;

function importData($host,$user,$pass,$dbname, $sql_file_OR_content) {

	set_time_limit(3000);
	$sql_content = (strlen($sql_file_OR_content) > 300 ?  $sql_file_OR_content : file_get_contents($sql_file_OR_content)  ); 
	$allLines = preg_split("/\\r\\n|\\r|\\n/", $sql_content);

	$mysqli = new mysqli($host, $user, $pass, $dbname);
	if (mysqli_connect_errno()) {
		echo "Không thể cập nhật data. Lỗi: " . mysqli_connect_error();
		return;
	} 

	$mysqli->query("SET NAMES 'utf8'");
	$templine = '';
	foreach ($allLines as $line) {
		// Loop through each line
		if (substr($line, 0, 2) != '--' && $line != '') {
			// (if it is not a comment..) Add this line to the current segment
			$templine .= $line; 	
			if (substr(trim($line), -1, 1) == ';') {
				// If it has a semicolon at the end, it's the end of the query
				// echo ' -- ' . $templine . EOL;
				if(!$mysqli->query($templine)) { 
					echo 'Error on query: ' . $mysqli->error . EOL;
				}  
				// set variable to empty, to start picking up the lines after ";"
				$templine = ''; 
			}
		}
	}	
	return 'Data đã được cập nhật vào hệ thống!';
}

?>