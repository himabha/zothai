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
}
if (empty($error_code)) {
    if (Wo_EmailExists($_POST['email']) === false) {
        $error_code    = 6;
        $error_message = 'Email not found';
    } else {
    	$user_recover_data         = Wo_UserData(Wo_UserIdFromEmail($_POST['email']));
		
		if(!empty($user_recover_data))
		{	
			$subject = $config['siteName'] . ' OTP';
			$forgot_password_otp = random_int(100000, 999999);
			
			$wo['recover'] = $user_recover_data;
			$wo['recover']['forgot_otp'] = $forgot_password_otp;
			$body = Wo_LoadPage('emails/otp');
			
			$query = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " set forgot_password_otp = '{$forgot_password_otp}' WHERE `user_id` = '{$user_recover_data['user_id']}' ");
			
			$send_message_data         = array(
				'from_email' => $wo['config']['siteEmail'],
				'from_name' => $wo['config']['siteName'],
				'to_email' => $_POST['email'],
				'to_name' => '',
				'subject' => $subject,
				'charSet' => 'utf-8',
				'message_body' => $body,
				'is_html' => true
			);
			$send = Wo_SendMessage($send_message_data);
			if ($send) {
				$response_data = array(
					'api_status' => 200,
					'message' => 'Email send successfully'
				);
			} else {
				$error_code    = 7;
				$error_message = 'Failed to send the email, please check your server email settings.';
			}
		} else {
			$error_code    = 7;
			$error_message = 'Email not found.';
		}	
    }
}