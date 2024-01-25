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
    $error_message = 'email is missing';
} else if (empty(Wo_Secure($_POST['password']))) {
    $error_code    = 3;
    $error_message = 'password is missing';
}

if (empty($error_code)) {
    if (Wo_EmailExists($_POST['email']) === false) {
        $error_code    = 6;
        $error_message = 'Email not found';
    } else {
    	$user_recover_data         = Wo_UserData(Wo_UserIdFromEmail($_POST['email']));
		$password = Wo_Secure(password_hash($_POST['password'], PASSWORD_DEFAULT));
		
		$query = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " set password = '{$password}' WHERE `user_id` = '{$user_recover_data['user_id']}' ");
		
		$response_data = array(
			'api_status' => 200,
			'message' => 'Password update successfully'
		);
    }
}