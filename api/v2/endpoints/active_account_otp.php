<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.wowonder.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | WoWonder - The Ultimate Social Networking Platform
// | Copyright (c) 2018 WoWonder. All rights reserved.
// +------------------------------------------------------------------------+
$response_data   = array(
    'api_status' => 400
);

$required_fields = array(
    'username',
    'code'
);
foreach ($required_fields as $key => $value) {
    if (empty($_POST[$value]) && empty($error_code)) {
        $error_code    = 3;
        $error_message = $value . ' (POST) is missing';
    }
}
if (empty($error_code)) {
	$confirm_code = $_POST['code'];
	$user_id      = $_POST['username'];

    $confirm_code = Wo_ConfirmUserOtp($user_id, $confirm_code);
    if (empty($confirm_code)) {
    	$error_code    = 3;
        $error_message = 'Wrong confirmation code.';
    }
    elseif($confirm_code === true){
		
		$type_table   = T_USERS; 
		$query_one    = mysqli_query($sqlConnect, "SELECT * FROM {$type_table} WHERE `email` = '{$user_id}'");
		$fetched_data = mysqli_fetch_assoc($query_one);
		
		$Fetch_UseiId = $fetched_data['user_id'];
        $time           = time();
        $cookie         = '';
        $access_token   = sha1(rand(111111111, 999999999)) . md5(microtime()) . rand(11111111, 99999999) . md5(rand(5555, 9999));
        $timezone       = 'UTC';
        $create_session = mysqli_query($sqlConnect, "INSERT INTO " . T_APP_SESSIONS . " (`user_id`, `session_id`, `platform`, `time`) VALUES ('{$Fetch_UseiId}', '{$access_token}', 'phone', '{$time}')");
        if (!empty($_POST['timezone'])) {
            $timezone = Wo_Secure($_POST['timezone']);
        }
        $add_timezone = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `timezone` = '{$timezone}',`active` = '1' WHERE `user_id` = {$Fetch_UseiId}");
        
        if (!empty($_POST['android_n_device_id'])) {
            $device_id  = Wo_Secure($_POST['android_n_device_id']);
            $update  = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `android_n_device_id` = '{$device_id}' WHERE `user_id` = '{$Fetch_UseiId}'");
        }
       
        if ($create_session) {
            $response_data = array(
                'api_status' => 200,
                'timezone' => $timezone,
                'access_token' => $access_token,
                'user_id' => $Fetch_UseiId,
            );
        }





    }

}