<?php
    /*
    *   DateTimeUtil
    *   Date: 2016-01-30
    *   Purpose: To handle all things related to date and time 
    */ 
    
class Util {
	/*
	
		Usage: create an UPDATE query for all data from POST
			$id = mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $_POST['task_id']);
			$query = Util::getUpdateQueryFromPOST('tasks', 'id', $id);
				
	*/
	static function getUpdateQueryFromPOST($table, $keyword, $keyvalue) {
	
		$result = mysqli_query(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), "SHOW COLUMNS FROM " . $table);
		$fields = array();
		$types = array();
		while($row = mysqli_fetch_array(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $result)){
			$fields[] = $row['Field'];
			$types[] = $row['Type'];
		}
	
		$data = '';
		$count = 0;
		foreach( $_POST as $field => $val ) {
				
			if( ($k = array_search($field, $fields)) !== false) {
				// found
				$type = $types[$k];
				$value = '\'' . mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $val) . '\'';
				if (stripos($type, 'nt') == 1)
					// integer type
					$value = mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $val);
				if ($count > 0) {
					$data .= ',';
				}
				$count++;
				$data .= $field . '=' . $value;
			}
		}
		$query = 'UPDATE ' . $table . ' SET ' . $data . ' WHERE ' . $keyword . '=' . $keyvalue;
		return $query;
	}
	
	/*
		Usage: create an INSERT query for all data from POST
			$query = Util::getInsertQueryFromPOST('customers');
				
	*/
	static function getInsertQueryFromPOST($table) {
	
		$result = mysqli_query(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), "SHOW COLUMNS FROM " . $table);
		$fields = array();
		$types = array();
		while($row = mysqli_fetch_array(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $result)){
			$fields[] = $row['Field'];
			$types[] = $row['Type'];
		}
	
		$fieldList = '';
		$data = '';
		$count = 0;
		foreach( $_POST as $field => $val ) {
				
			if( ($k = array_search($field, $fields)) !== false) {
				// found
				$type = $types[$k];
				$value = '\'' . mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $val) . '\'';
				if (stripos($type, 'nt') == 1)
					// integer type
					$value = mysqli_real_escape_string(mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME), $val);
	
				if ($count > 0) {
					$fieldList .= ',';
					$data .= ',';
				}
				$fieldList .= $field;
				$data .= $value;
				$count++;
			}
		}
			
		$query = 'INSERT INTO ' . $table . ' (' . $fieldList . ') VALUES (' . $data . ')';
		return $query;
	}
	/*
		Usage: sort an array which contains field "cdate"
			usort($array, "Util::sortDate");

	*/
	static function sortDate($a, $b) {
		return strtotime($b["cdate"]) - strtotime($a["cdate"]);
	}
	
	static function dateTodayToDisplay() {
		// input:  date() -> output: '25/12/15'
		return date('d/m/y');
	}
	
	static function dateTodayToDB() {
		// input:  date() -> output DB : '2015-12-25 00:00:00'
		// DB is 'timestamp' data in database
		return date('Y-m-d H:i:s');
	}
	
	static function dateDBToMoreDaysDB($date, $extraDays) {
		// 24 hour * 60 minutes
		return static::dateDBToDB($date, "00:00", $extraDays * 24 * 60);
	}
	
	static function dateDBToMoreHoursDB($date, $extraHours) {
		// 1 hour * 60 minutes
		return static::dateDBToDB($date, null, $extraHours * 60);
	}

	static function dateDBToDB($date, $hourMinute, $extraMinute) {
		// input:  date() -> output: '2015-12-25 hourMinute:00'
	
		$startDate = date('Y-m-d', strtotime($date));
		//logError('date, hourMinute = ' . $startDate . ',' . $hourMinute);
		$time = strtotime($startDate);
		if ($hourMinute == null) {
			// use current date time
			$hourMinute = date('H:i', strtotime($date));
		}
		$minutes = substr($hourMinute, 0, 2) * 60 + substr($hourMinute, 3, 2);
		$minutes += $extraMinute;
		//logError('minute = ' . $minutes);
	
		$time = strtotime('+' . $minutes . ' minutes', $time);
		//$fullDate = date('Y-m-d H:i:s', $time);
		//return substr_replace($fullDate,$hourMinute,11,5);
		return date('Y-m-d H:i:s', $time);
	}
	
	static function dateDBDateToDBDatetime($date) {
		return date('Y-m-d H:i:s', strtotime($date));
	}
	
	static function dateThisMonthToDB() {
		// input:  date() -> output: '2015-12-01 00:01:00'
		$today = Util::dateTodayToDB();
		return substr_replace($today,'01 00:01:00',8,11);
	}
	
	static function dateNextMonthToDB() {
		// One month from a specific date
		// $date = date('Y-m-d', strtotime('+1 month', strtotime('2015-01-01')));
		// One month from today
		$date = date('Y-m-d H:i:s', strtotime('+1 month'));
		return substr_replace($date,'01 00:01:00',8,11);
	}
	
	static function dateDBToDisplay($date, $hourMinute) {
		//logError('date timestamp = ' . $date . ',' . date('d/m/y H:i', strtotime($date)) . ',' . $hourMinute);
		// input:  '2015-12-25 00:00:00' -> output: '25/12/15' , '25/12/15 00:00'
		if ($date == null || $date == '0000-00-00 00:00:00')
			return '';
		//return $hourMinute ? date('d/m/y H:i', strtotime($date)) : date('d/m/y', strtotime($date));
		return $hourMinute ? date('d-M-Y H:i', strtotime($date)) : date('d-M-Y', strtotime($date));
	}
	
	static function dateDBToHourMinuteDisplay($date) {
		// input:  '2015-12-25 12:30:10' -> output: '12:30'
		$date = date('d/m/y H:i', strtotime($date));
		return substr($date, 9, 5);
	}
	
	static function dateDBToSortDate($date) {
		// input:  '2015-12-25 00:00:00' -> output: '20151225'
		if ($date == '0000-00-00 00:00:00')
			return '0';
		return substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
	}
	
	static function dateDBToPicker($date) {
		// input:  '2015-12-25 00:00:00' -> output: '12/25/2015'
		if ($date == '0000-00-00 00:00:00')
			return '';
		//return date('m/d/Y', strtotime($date));
		return static::dateDBToDisplay($date, false);
	}
	
	static function dateGETToDB($date) {
		// input:  '2015-12-25%2000:00:00' -> output: '2015-12-25 00:00:00'
		return str_replace("%20"," ",$date);
	}
	
	static function dateDBToCalendar($date) {
		// input: '2015-09-30 08:00:00' to '2015-09-30T08:00:00' for FullCalendar
		$vals = explode(' ', $date);
		return $vals[0] . 'T' . $vals[1];
	}
	
	static function dateDisplayToDB($date) {
		// input: '13/02/2018 (14:49)' to '2018-02-13 14:49:00'
		$day = substr($date, 0, 2);
		$month = substr($date, 3, 2);
		$year = substr($date, 6, 4);
		if (strlen($date) < 11) {
			$hour = '00';
			$minute = '00';
		} else {
			$hour = substr($date, 12, 2);
			$minute = substr($date, 15, 2);
		}
		return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . '00';
	}

	static function priceToDONG($price) {
		// check if a dot is found!
		$pos = strpos($price, '.');
		if ($pos === false)
			// no dot, call integer conversion
			return number_format($price, 0 , "," , ".") . ' Đ';

		// just add dong sign
		$price = $price . ' Đ';
		return $price;
    } 

    static function priceIntegerToDONG($price) {
		return number_format($price, 0 , "," , ".") . ' Đ';		
    }

    static function stringToPrice($price) {
    	// input: '12500' to '12.500'
    	$price = trim($price);
    	return number_format($price, 0 , "," , ".");
    }
	
}
?>