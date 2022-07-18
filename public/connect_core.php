<?php
header('Access-Control-Allow-Origin: *');

class connect_core {
    // Config to query
    public static function sqlQuery($sql){
        $model = connect_core::sqlConnect();
        $data = mysqli_query($model, $sql);
        if (!mysqli_num_rows($data)) {
			$status = "OK";
			$info = array('rows' => array());
			$message = "No record for query: " . $sql;
				
		} else {
			$status = "OK";
			while($row = mysqli_fetch_assoc($data))
				$rows[] = $row;
			$info = array('rows' => $rows);
            $message = "There are " . count($info) . " records for query!";
        }
        return array('status'=>$status, 'info'=>$info, 'message'=>$message);
    }

    private static function real_escape_string($data){
        $con = connect_core::sqlConnect();
        $result = mysqli_real_escape_string($con, $data);
        return $result;
    }

    private static function sqlConnect(){
        $model = new mysqli(DB_HOST_CORE, DB_USER_CORE, DB_PASSWORD_CORE, DB_NAME_CORE);
        if (mysqli_connect_errno()) {
            echo "Không thể cập nhật data. Lỗi: " . mysqli_connect_error();
            return;
        } 
        $model->query("SET NAMES 'utf8'");
        return $model;
    }
}