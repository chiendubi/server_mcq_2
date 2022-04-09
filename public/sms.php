<?php
require_once(SERVER_ROOT . '/lib/speedsms/index.php');
class Sms{
    public static function send_sms($phone, $content, $chain_code = ''){
        $result = array();
        $chain_name = '';
        if($chain_code){
            $chain = Sms::getInformationChain($chain_code);
            $$content = $chain['name'] ? '['.strtoupper($chain['name']).'] '.$content : $content;
        }
        if (LOCAL_DATA){
            $phone = '+84352158411';  
        }
        $sms = sendSMS($phone, $content);
        if($sms['status'] == 'success'){
            $result['status'] = 'OK';
            $result['message'] = 'Success'; 
        } else {
            $result['status'] = 'ERROR';
            $result['message'] = 'ERROR - ' . $sms['message'] . '!';
        }
        return $result;
    }
    public static function send_sms_for_password($phone, $new_password, $chain_code = ''){
        $result = array();
        $chain_name = '';
        if($chain_code){
            $chain = Sms::getInformationChain($chain_code);
            $chain_name = $chain['name'] ? '['.strtoupper($chain['name']).'] ' : '';
        }
        $content = $chain_name .'Mat khau đang nhap vao he thong cua ban: '.$new_password;
        if (LOCAL_DATA){
            $phone = '+84352158411';  
        }
        $sms = sendSMS($phone, $content);
        if($sms['status'] == 'success'){
            $result['status'] = 'OK';
            $result['message'] = 'Success'; 
        } else {
            $result['status'] = 'ERROR';
            $result['message'] = 'ERROR - ' . $sms['message'] . '!';
        }
        return $result;
    }
    public static function getInformationChain($chain_code){
        $chain = connect_core::sqlQuery('
            SELECT chain_translation.name
            FROM chain 
            LEFT JOIN chain_translation ON (chain.code = chain_translation.chain_code)
            WHERE chain.code = "'.$chain_code.'" AND chain_translation.language_code = "'.LANGUAGE.'" 
            LIMIT 1
        ');
        $chain = isset($chain['info']['rows']) ? $chain['info']['rows'][0] : array();
        return $chain;
    }
}
?>
