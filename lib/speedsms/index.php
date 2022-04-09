<?php
const API_ACCESS_TOKEN = 'RqGwxVDsKitkO6Q8s5YUM2XXkq7LMXmB';
require("SpeedSMSAPI.php");
require("TwoFactorAPI.php");

function getUserInfo() {
    $sms = new SpeedSMSAPI(API_ACCESS_TOKEN);
    $userInfo = $sms->getUserInfo();
    // var_dump($userInfo);
    return $userInfo;
}

// function testSMS($phone, $content) {
//     return 'testSMS';
// }

function sendSMS($phone, $content) {
    $sms = new SpeedSMSAPI(API_ACCESS_TOKEN);
    $return = $sms->sendSMS([$phone], $content, SpeedSMSAPI::SMS_TYPE_CSKH, "");
    // var_dump($return);
    return $return;
}

function createPIN($phone, $content, $appId) {
    $twoFA = new TwoFactorAPI();
    $result = $twoFA->pinCreate($phone, $content, $appId);
    // var_dump($result);
    return $result;

}

function verifyPIN($phone, $pinCode, $appId) {
    $twoFA = new TwoFactorAPI();
    $result = $twoFA->pinVerify($phone, $pinCode, $appId);
    // var_dump($result);
    return $result;
}