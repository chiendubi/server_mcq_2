<?php

    // include('class.smtp.php');
    // include "class.phpmailer.php"; 

    // $to_id = 'lethanhluan54cntt@gmail.com';
    // $subject = 'HP Clinic';
    // $content = 'Mật khẩu mới';
    // $fileName = array();
    // $mFrom = 'esmilesdental@gmail.com';  //dia chi email cua ban 
    // $mPass = 'e10smiles1000';       //mat khau email cua ban
    // sendMail($subject, $content, $mFrom, $mPass, $to_id, $fileName);

function sendMail($title, $content, $mFrom, $mPass, $mTo, $fileName){
    $nFrom = 'do-not-reply '.M_Name;
    // $mFrom = 'esmilesdental@gmail.com';  //dia chi email cua ban 
    // $mPass = 'e10smiles1000';       //mat khau email cua ban
    // $mFrom = 'dr.pnhuy@gmail.com';  //dia chi email cua ban 
    // $mPass = 'Beobenbimhuy15141107';       //mat khau email cua ban
    $mail             = new PHPMailer(true);
    $body             = $content;
    $mail->IsSMTP(); 
    $mail->CharSet   = "utf-8";
    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
    $mail->SMTPAuth   = true;                    // enable SMTP authentication
    $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
    $mail->Host       = "pro50.emailserver.vn";        
    $mail->Port       = 465;
    $mail->Username   = $mFrom;  // GMAIL username
    $mail->Password   = $mPass;               // GMAIL password
    $mail->SetFrom($mFrom, $nFrom);
    //chuyen chuoi thanh mang
    // $ccmail = explode(',', $diachicc);
    // $ccmail = array_filter($ccmail);
    // if(!empty($ccmail)){
    //     foreach ($ccmail as $k => $v) {
    //         $mail->AddCC($v);
    //     }
    // }
    $mail->Subject    = $title;
    $mail->MsgHTML($body);
    $address = $mTo;
    $nTo = '';
    $mail->AddAddress($address, $nTo);
    if(count($fileName) > 0){
        foreach($fileName as $fn){
            logError($fn);
            $attachment = PRODUCT_IMAGE_PATH . $fn;
            $mail->AddAttachment($attachment, $fn, $encoding ='base64',$type = 'application/octet-stream');
        }
    }
    // $mail->AddReplyTo('info@freetuts.net', 'Freetuts.net');
    if(!$mail->Send()) {
        return 0;
    } else {
    //    if($mail->Host == "pro50.emailserver.vn" ){
    //         save_mail($mail);
    //     } 
        return 1;
    }
}
function save_mail($mail)
{
    //You can change 'Sent Mail' to any other folder or tag
    $path = "{mail.esmiles.vn:993/imap/ssl/novalidate-cert}INBOX.Sent";
    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
    $imapStream = imap_open($path, $mail->Username, $mail->Password);
    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
    imap_close($imapStream);
    return $result;
}
 