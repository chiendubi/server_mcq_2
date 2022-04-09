<?php
require_once(SERVER_ROOT . '/lib/PHPMailer/class.smtp.php');
require_once(SERVER_ROOT . '/lib/PHPMailer/class.phpmailer.php');
require_once(SERVER_ROOT . '/lib/PHPMailer/sendMail.php');
class Email{
    public static function send_mail_for_password($email, $new_password){
        // Send mail for account
        // set up and send mail
        $title = 'Mật khẩu của bạn!';
        $subtitle = '';
        $content = $new_password;
        $message = file_get_contents(SERVER_ROOT . '/lib/email_template/mail_tmp.html'); 
        $message = str_replace('%title%', $title, $message); 
        $message = str_replace('%subtitle%', $subtitle, $message); 
        $message = str_replace('%content%', $content, $message); 
        $subject =  'Mật khẩu';
        $array = array();
        $result = array();
        $result['value'] = sendMail($subject, $message, M_From, M_Pass, $email, $array);
        $result['password'] = $new_password;
        return $result;
    }

    public static function send_mail_for_information($email, $subject, $title, $subtitle, $content, $attachments = array()){
        $result['status'] = 'ERROR';
        $message = file_get_contents(SERVER_ROOT . '/lib/email_template/mail_tmp.html'); 
        $message = str_replace('%title%', $title, $message); 
        $message = str_replace('%subtitle%', $subtitle, $message); 
        $message = str_replace('%content%', $content, $message); 
        // set up and send mail
        $array = array();
        if(isset($attachments)){
            $array = $attachments;
        }
        $result = array();
        $email = sendMail($subject, $message, M_From, M_Pass, $email, $array);
        if($email === 1){
            $result['value'] = 1;
            $result['status'] = 'OK';
        }
        return $result;
    }
    public static function send_mail_for_information_V1($email, $subject, $title, $subtitle, $content, $attachments = array(), $url_has_params = ""){
        if(LOCAL_DATA){
            // logError('url_has_params-title:'.print_r($url_has_params, true).'-'.print_r($title, true));
            // logError('send_to_real_email:'. print_r($email, true));
            // $email = 'chienld@esmiles.vn';
            // logError('subtitle:'.print_r($subtitle, true));
        }else{
            // logErrorDebug('url_has_params-title:'.print_r($url_has_params, true).print_r($title, true));
            // logErrorDebug('email:'.print_r($email, true));
            // logErrorDebug('subtitle:'.print_r($subtitle, true));
        }
        $result['status'] = 'ERROR';
        $message = file_get_contents(SERVER_ROOT . '/lib/email_template/mail_tmp_V1.html'); 
        $message = str_replace('%title%', $title, $message); 
        $message = str_replace('%subtitle%', $subtitle, $message); 
        $message = str_replace('%content%', $content, $message); 
        if(isset($url_has_params)){
            $message = str_replace('%url_has_params%', $url_has_params, $message); 
        }
        // set up and send mail
        $array = array();
        if(isset($attachments)){
            $array = $attachments;
        }
        $result = array();
        $email = sendMail($subject, $message, M_From, M_Pass, $email, $array);
        if($email === 1){
            $result['value'] = 1;
            $result['status'] = 'OK';
        }
        return $result;
    }
}
?>