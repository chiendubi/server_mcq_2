<?php
header('Access-Control-Allow-Origin: *');

class Utility {

    // http://www.media-division.com/correct-name-capitalization-in-php/
    // public static function titleCase($string) {
    
    public static function getProperName($string) {

        $string = trim($string);
        if (strlen($string) == 0)
            return '';

        // trim and remove extra blank
        $string = str_replace("  "," ", $string);

        $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
        $lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
        $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');
 
        $string = strtolower($string);
        foreach ($word_splitters as $delimiter) 
        { 
            $words = explode($delimiter, $string); 
            $newwords = array(); 
            foreach ($words as $word)
            { 
                if (in_array(strtoupper($word), $uppercase_exceptions))
                    $word = strtoupper($word);
                else
                if (!in_array($word, $lowercase_exceptions))
                    $word = ucfirst($word); 
 
                $newwords[] = $word;
            }
 
            if (in_array(strtolower($delimiter), $lowercase_exceptions))
                $delimiter = strtolower($delimiter);
 
            $string = join($delimiter, $newwords); 
        } 
        return $string; 
    }
    
    public static function getProperEmail($email) {

        $email = trim($email);
        if (strlen($email) == 0)
            return '';

        // trim and set to lower
        $email = strtolower($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ## logError("email not valid: " . $email);
            $email = '';
        }
        return $email;
    }

    public static function getProperPhone($phone) {

        // trim and check max 8 digits, Hong Kong
        $phone = trim($phone);
        if (strlen($phone) == 0)
            return '';
        // remove - and space
        $phone = str_replace("-","", $phone);
        $phone = str_replace(" ","", $phone);

        // 1st digit can not be 0, max 10 digits
        // if (preg_match('/^[1-9][0-9]{0,10}$/', $phone))
        // 1st digit can be 0, max 15 digits
        if (preg_match('/^[0-9][0-9]{0,15}$/', $phone))
            return $phone;
        ## logError("phone not valid: " . $phone);
        return '';
    }

    public static function convert_number_to_words( $number ){
        $hyphen = ' ';
        $conjunction = '  ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín',
            10 => 'Mười',
            11 => 'Mười một',
            12 => 'Mười hai',
            13 => 'Mười ba',
            14 => 'Mười bốn',
            15 => 'Mười năm',
            16 => 'Mười sáu',
            17 => 'Mười bảy',
            18 => 'Mười tám',
            19 => 'Mười chín',
            20 => 'Hai mươi',
            30 => 'Ba mươi',
            40 => 'Bốn mươi',
            50 => 'Năm mươi',
            60 => 'Sáu mươi',
            70 => 'Bảy mươi',
            80 => 'Tám mươi',
            90 => 'Chín mươi',
            100 => 'trăm',
            1000 => 'nghìn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );
    
        if( !is_numeric( $number ) )
        {
            return false;
        }
    
        if( ($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX )
        {
            // overflow
            trigger_error( 'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING );
            return false;
        }
    
        if( $number < 0 )
        {
            return $negative . Utility::convert_number_to_words( abs( $number ) );
        }
    
        $string = $fraction = null;
    
        if( strpos( $number, '.' ) !== false )
        {
            list( $number, $fraction ) = explode( '.', $number );
        }
    
        switch (true)
        {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if( $units )
                {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if( $remainder )
                {
                    $string .= $conjunction . Utility::convert_number_to_words( $remainder );
                }
                break;
            default:
                $baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = Utility::convert_number_to_words( $numBaseUnits ) . ' ' . $dictionary[$baseUnit];
                if( $remainder )
                {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= Utility::convert_number_to_words( $remainder );
                }
                break;
        }
    
        if( null !== $fraction && is_numeric( $fraction ) )
        {
            $string .= $decimal;
            $words = array( );
            foreach( str_split((string) $fraction) as $number )
            {
                $words[] = $dictionary[$number];
            }
            $string .= implode( ' ', $words );
        }
    
        return $string;
    }

    public static function exchange_Rate(){
        $url = "https://www.vietcombank.com.vn/exchangerates/ExrateXML.aspx";
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  
        $xml = file_get_contents($url, false, stream_context_create($arrContextOptions));
        $data = simplexml_load_string($xml);

        $time_update = $data->DateTime;
        $ex_rate = $data->Exrate;
        $transfer = array();
        foreach($ex_rate as $currency) {
            // $ma = $ngoai_te['CurrencyCode'];
            if($currency['CurrencyCode'] == "USD"){
                $transfer_tmp = explode('.',  $currency['Transfer']);
                $transfer_tmp = str_replace(',', '', $transfer_tmp[0]);
                break;
            }
        }
        $transfer_tmp = json_decode(json_encode((array)$transfer_tmp), TRUE);
        $date_tmp = json_decode(json_encode((array)$time_update), TRUE);
        $date = date_create($date_tmp[0]);
        $date = date_format($date,"Y-m-d H:i:s");
        $transfer['date'] = $date;
        $transfer['transfer'] = $transfer_tmp[0];
        return $transfer;
    }

    public static function mb_ucfirst($string, $encoding='UTF-8') { 
        $firstChar = mb_substr($string, 0, 1, $encoding); 
        $then = mb_substr($string, 1, mb_strlen($string, $encoding)-1, $encoding); 
        return mb_strtoupper($firstChar, $encoding) . $then; 
    } 
    /**
     * Convert: 'esmiles company' => Esmiles Company
     */
    public static function firstCharString($string, $encoding='UTF-8'){ 
        $string = Utility::trimMultipleSpaces($string); 
        $aString = explode(' ', $string); 
        $sReturn = ''; 
        foreach ($aString as $k=>$fString) { 
            $firstChar = mb_substr($fString, 0, 1, $encoding); 
            $then = mb_substr($fString, 1, mb_strlen($fString, $encoding)-1, $encoding); 
            $sReturn .= mb_strtoupper($firstChar, $encoding) . $then.' '; 
        } 
        return trim($sReturn); 
    } 
    public static function firstCharStringCode($string, $encoding='UTF-8'){
        $aString = explode(' ', $string); 
        $sReturn = ''; 
        foreach ($aString as $k=>$fString) { 
            $firstChar = mb_substr($fString, 0, 1, $encoding); 
            $then = mb_substr($fString, 1, mb_strlen($fString, $encoding)-1, $encoding); 
            $sReturn .= mb_strtoupper($firstChar, $encoding); 
        } 
        return Utility::convert_vi_to_en(trim($sReturn)); 
    }
    /**
     * Delete a lot of spaces
     * Convert: '   esmiles       company   ' => esmiles company
     */
    public static function trimMultipleSpaces($string, $encoding='UTF-8'){
        $aString = preg_replace('/\s+/', ' ', trim($string, ' '));
        return $aString; 
    }
    
    public static function create_qr($qr_name, $content){ 
        require_once(SERVER_ROOT . '/lib/phpqrcode/qrlib.php');
        $tempDir = PRODUCT_IMAGE_PATH;
        QRcode::png($content, $tempDir.$qr_name.'.png', QR_ECLEVEL_H, 4); 
        $img = file_get_contents($tempDir.$qr_name.'.png'); 
        $base64 ='data:image/png;base64,'.base64_encode($img);
        unlink($tempDir.$qr_name.'.png');
        return $base64;
    }
    
    public static function convert_vi_to_en($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        return $str;
    }
    public static function generatePage($total, $pageSize) {
        $result =  array();
        $countMod = $total%$pageSize;
        $countInt = $countMod > 0 ? ($total - $countMod)/$pageSize + 1 : ($total - $countMod)/$pageSize;
        for ($i=1; $i <= $countInt; $i++)
        {
            $temp = Array();
            $temp['pageNo'] = $i;

            if($i == 1){
                $temp['isActive'] = true;
            }else{
                $temp['isActive'] = false;
            }
           
            if($i == $countInt && $countMod > 0){
                $temp['pageSize'] = $pageSize;
                $temp['quantityRecord'] = $countMod;
            }else{
                $temp['pageSize'] = $pageSize;
                $temp['quantityRecord'] = $pageSize;
            }
            $result[] = $temp;
        }
               
        return $result;
    }
    public static function callLocalFunction($mod, $route, $controller, $action){

        ob_start();
        require_once(SERVER_ROOT . '/commands/'.$mod.'/'.$route.'/'.$controller.'.php');
        $call = new $controller();
        $call->beforeAction($action);
        $data = ob_get_contents();
        ob_end_clean();
        $adata = json_decode($data,true);
        _json_echo('callLocalFunction', $adata);
    }
    public static function getOptionDynamic($mod, $route, $controller, $action){
        ob_start();
        Utility::callLocalFunction($mod, $route, $controller, $action);
        $data = ob_get_contents();
        ob_end_clean();
        $response = json_decode($data,true);
        return $response['data'];
    }

    ## Processed data 
    ## Add a new parameter to $_POST['data']
    public static function processedAddParameterToPost($parameter){
        $sql_model = new VanillaModel();
        $data = array();
        if(isset($_POST['data'])){
            $data = $sql_model->real_escape_string($_POST['data']);
            $data = str_replace('\"', '"', $data);
            $data = json_decode($data, true);
        }
        foreach ($parameter as $key => $value) {
            $data[$key] = $value;
        }
        $_POST['data'] = json_encode($data, true);
    }
    public static function processedData($hasFieldJson = false){
        $sql_model = new VanillaModel();
        $data = $sql_model->real_escape_string($_POST['data']);
        $data = str_replace('\"', '"', $data);
        if($hasFieldJson){
            $data = str_replace('\"', '"', $data);
        }
        return json_decode($data, true);
    }

    public static function processedSaveData($table, $data, $skip = array(), $json = array()){
        $sql_model = new VanillaModel();
        $field_list = '';
        $value_list = '';
        $update_str = '';
        $sql_str = '';
        $skip_arr = array();
        $json_arr = array();
        if(isset($skip)){
            $skip_arr = $skip;
        }
        if(isset($json)){
            $json_arr = $json;
        }
        foreach($data as $key=>$value) {
            if($key == 'id'){
                continue;
            }else{
                if(count($skip_arr) > 0){
                    if(array_search($key, $skip_arr) > -1){
                        continue; 
                    }
                }
                if($data['id'] == 0){
                    $field_list .= ",$key";
                    if(array_search($key, $json_arr) > -1){
                        $value_list .= ",". $value ."";
                    }else{
                        $value_list .= ",'". $value ."'";
                    }
                }else{
                    if(array_search($key, $json_arr) > -1){
                        $update_str .= "$key = ". $value .",";
                    }else{
                        $update_str .= "$key = '". $value ."',";
                    }
                }
            }
        }
        if($data['id'] == 0){
            $sql_str = ('
                INSERT INTO '.$table.' ('.trim($field_list, ',').') 
                VALUES ('.trim($value_list, ',').')
            ');
        }else{
            if(!empty(trim($update_str, ','))){
                $sql_str = ('
                    UPDATE '.$table.' SET '.trim($update_str, ',').'
                    WHERE id = '.$data['id'].'
                ');
            }
        }
        if(!empty($sql_str)){
            return $sql_model->queryWithStatus($sql_str);
        }else{
            $result = array();
            $result['status'] = "OK";
            return $result;
        }
    }
    public static function processedDeleteData($table , $data){
        $sql_model = new VanillaModel();
        $count_total = count($data);
        $delete_str = '';
        $i = 0;
        foreach($data as $key=>$value) {
            $delete_str .= " $key = '".$sql_model->real_escape_string($value)."' AND";
        }
        $sql_str = '
            DELETE FROM '.$table.'
            WHERE '.trim($delete_str, " AND").'
        ';
        return $sql_model->queryWithStatus($sql_str);
    }
    public static function processedAutoInsertConstant($table, $field, $data){
        $sql_model = new VanillaModel();
        if (isset($data) && count($data) > 0) {
            for ($i=0; $i < count($data); $i++) { 
                $sql = 'SELECT * FROM '.$table.' WHERE '.$field.' = "'. $data[$i].'"';
                $result = $sql_model->queryWithResultSet($sql);
                if (count($result['info']['rows']) == 0) {
                    $sql = 'INSERT INTO '.$table.'('.$field.') VALUES ("'.$data[$i].'")';
                    $insert = $sql_model->queryWithStatus($sql);
                }
            }
        };
        $response = 'OK';
        return $response;
    }
    /**
     * $type = true:  generate code for parent 
     * ex: company esmiles => CE0VB
     * $type = false: use code from parent 
     * ex: CE0VB + time() => CE0VB202008909012
     */
    public static function processedCheckField($table, $field, $fieldCondition, $type = false){
        $sql_model = new VanillaModel();
        if(!$type){
            $code = $fieldCondition . time();
        }else{
            $str = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 3)), 0, 3);
            $code = Utility::firstCharStringCode($fieldCondition).$str;
        }
        $checkCode =  $sql_model->queryWithOneResultSet('
            SELECT '.$field.' FROM '.$table.' WHERE '.$field.' = '.$code.'
        ');
        if($checkCode){
            Utility::processedCheckField($table, $field, $fieldCondition);
        }else{
            return $code;
        }
    }
    public static function processedQueryDataList($table, $query, $condition){
        $sql_model = new VanillaModel();
        $searchQuery = $query;
        $conditionDefault = '';
        $draw = '';
        ## Search 
        if($condition != ''){
            $searchQuery .= $condition;
        }
        ## Read value
        if(isset($_POST['draw'])){
            $draw = $sql_model->real_escape_string($_POST['draw']);
            $row = $sql_model->real_escape_string($_POST['start']);
            $rowperpage = $sql_model->real_escape_string($_POST['length']); // Rows display per page
            $columnIndex = $sql_model->real_escape_string($_POST['order'][0]['column']); // Column index
            $columnName = $sql_model->real_escape_string($_POST['columns'][$columnIndex]['data']); // Column name
            $columnSortOrder = $sql_model->real_escape_string($_POST['order'][0]['dir']); // asc or desc
            $columns = $_POST['columns'];
            $searchValue = $sql_model->real_escape_string($_POST['search']['value']); // Search value 
            $searchValue = Utility::trimMultipleSpaces($searchValue);
            if($searchValue != ''){
                $searchQueryDefault = "";
                foreach($columns as $c){
                    if($c['searchable'] === 'true'){
                        $searchQueryDefault .= "OR ". $c['data'] ." LIKE '%". $searchValue. "%' ";
                    }
                }
                $searchQueryDefault = trim($searchQueryDefault, 'OR');
                $searchQuery =  $searchQuery . " and (" . $searchQueryDefault . ")";
            }
            $conditionDefault = " ORDER BY " .$columnName. " ".$columnSortOrder;
            if($rowperpage > -1){
                $conditionDefault .= " LIMIT ".$row.", ".$rowperpage;
            }
        }
        ## Total number of records without filtering
        $records = $sql_model->queryWithOneResultSet('SELECT count(*) as allcount FROM '.$table.'');
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $totalFilter = explode('FROM', $searchQuery, 2)[1];
        $records = $sql_model->queryWithOneResultSet('SELECT count(*) as allcount FROM '.$totalFilter.'');
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records  
        $empQuery = $searchQuery.$conditionDefault;
        logError('empQuery:'.print_r($empQuery, true));
        $result = $sql_model->custom($empQuery);
        $response = array(
            'draw' => $draw,
            'data' => $result,
            'totalRecords' => $totalRecords,
            'totalRecordwithFilter' => $totalRecordwithFilter
        );
        return $response;
    }
    public static function processedConvertDataDisplay($table, $queryColumn, $data){
        $sql_model = new VanillaModel();
        $condition = '';
        $out = array();
        if(count($data) > 0){
            foreach($data as $i){
                $condition .= 'OR '.$queryColumn.' = "'.$i.'" ';
            }
            $condition = trim($condition, 'OR');
            $result = $sql_model->queryWithResultSet('
                SELECT name FROM '.$table.'
                WHERE ('. $condition .') AND language_code = "'.LANGUAGE.'"
            ');       
            $result = $result['info']['rows'];
            if(count($result) > 0){
                foreach($result as $r){
                    $out[] = $r['name'];
                }
            }
        }
        return $out;
    }
    public static function convertDataJSON($data){
        $str = '';
        foreach($data as $key => $value){
            if(gettype($value) == 'array'){
                if(gettype($key) === 'string'){
                    $str .= 'JSON_OBJECT("'.$key.'", JSON_ARRAY('.Utility::convertDataJSON($value).')),';
                }else{
                    $str .= 'JSON_MERGE('.Utility::convertDataJSON($value).'),';
                }
            }else{
                if(gettype($key) === 'string'){
                    $str .= 'JSON_OBJECT("'.$key.'", "'.$value.'"),';
                }else{
                    $str .= '"'.$value.'",';
                }
            }
        }
        return trim($str, ',');
    }
    // public static function convertDataJSON($data){
    //     $str = '';
    //     foreach($data as $key => $value){
    //         logError('type: ' .print_r(Utility::is_array_check($value), true));
    //         if ( Utility::is_array_check($value) == 'array') {
    //             $str .= 'JSON_OBJECT("'.$key.'", JSON_MERGE('.Utility::convertDataJSON($value).')),';
    //         }else if(Utility::is_array_check($value) == 'object'){
    //             $str .= 'JSON_ARRAY('.Utility::convertDataJSON($value).'),';
    //         }else {
    //             if(gettype($key) === 'string'){
    //                 $str .= 'JSON_OBJECT("'.$key.'", "'.$value.'"),';
    //             }else{
    //                 $str .= '"'.$value.'",';
    //             }
    //         }
    //         // if(gettype($value) == 'array'){
    //         //     if(gettype($key) === 'string'){
    //         //         $str .= 'JSON_OBJECT("'.$key.'", JSON_ARRAY('.Utility::convertDataJSON($value).')),';
    //         //     }else{
    //         //         $str .= 'JSON_MERGE('.Utility::convertDataJSON($value).'),';
    //         //     }
    //         // }else{
    //         //     if(gettype($key) === 'string'){
    //         //         $str .= 'JSON_OBJECT("'.$key.'", "'.$value.'"),';
    //         //     }else{
    //         //         $str .= '"'.$value.'",';
    //         //     }
    //         // }
    //     }
    //     return trim($str, ',');
    // }
    public static function is_array_check($array) {
        $next = 0;
        if(is_array($array) > 0){
            foreach ( $array as $k => $v ) {
                if ( $k !== $next ) return 'object';
                $next++;
            }
            return 'array';
        }else{
            return 'string';
        }
    }
}
?>