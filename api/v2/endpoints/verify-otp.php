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
$response_data = array(
    'api_status' => 400,
);
if (empty($_POST['email'])) {
    $error_code    = 3;
    $error_message = 'email (POST) is missing';
} else if (empty($_POST['otp'])) {
    $error_code    = 3;
    $error_message = 'otp (POST) is missing';
}
if (empty($error_code)) {
    if (Wo_EmailExists($_POST['email']) === false) {
        $error_code    = 6;
        $error_message = 'Email not found';
    } else {
    	$user_recover_data         = Wo_UserData(Wo_UserIdFromEmail($_POST['email']));
		
		if(!empty($user_recover_data))
		{
			if ($user_recover_data['forgot_password_otp'] == $_POST['otp']) {
				$response_data = array(
					'api_status' => 200,
					'message' => 'OTP has been verified successfully.'
				);
			} else {
				$error_code    = 3;
				$error_message = 'Provided OTP is wrong.';
			}
		} else {
			$error_code    = 7;
			$error_message = 'Email not found.';
		}	
    }
}