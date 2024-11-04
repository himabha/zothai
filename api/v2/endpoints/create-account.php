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
    'phone_num',
    'password',
    'email',
    'confirm_password',
    'profile_type'
);
foreach ($required_fields as $key => $value) {
    if (empty($_POST[$value]) && empty($error_code)) {
        $error_code    = 3;
        $error_message = $value . ' (POST) is missing';
    }
}
if (empty($error_code)) {
    //$username         = $_POST['username'];
    $password         = $_POST['password'];
    $email            = $_POST['email'];
    $confirm_password = $_POST['confirm_password'];
    $profile_type = $_POST['profile_type'];
    /*if (in_array(true, Wo_IsNameExist($username, 0))) {
        $error_code    = 4;
        $error_message = 'Username is already taken';
    } else if (in_array($username, $wo['site_pages']) || !preg_match('/^[\w]+$/', $username)) {
        $error_code    = 5;
        $error_message = 'Invalid username characters, please choose another username';
    } else if (strlen($username) < 5 OR strlen($username) > 32) {
        $error_code    = 6;
        $error_message = 'Username must be between 5 / 32 letters';
    } else*/ 
    if (Wo_EmailExists($email) === true) {
        $error_code    = 7;
        $error_message = 'E-mail is already taken';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_code    = 8;
        $error_message = 'E-mail is invalid';
    } else if (strlen($password) < 6) {
        $error_code    = 9;
        $error_message = 'Password is too short';
    } else if ($password != $confirm_password) {
        $error_code    = 10;
        $error_message = 'Passwords don\'t match';
    }
    if (!empty($_POST['phone_num'])) {
            if (!preg_match('/^\+?\d+$/', $_POST['phone_num'])) {
                $errors = $error_icon . $wo['lang']['worng_phone_number'];
            } else {
                if (Wo_PhoneExists($_POST['phone_num']) === true) {
                    $errors = $error_icon . $wo['lang']['phone_already_used'];
                }
            }
        }
    if (empty($error_code)) {


        $profile_type = '1';
        if (isset($_POST['profile_type']) && !empty($_POST['profile_type'])) {
            $profile_type = $_POST['profile_type'];
        }
        $username='';
        if (isset($_POST['first_name']) && !empty($_POST['first_name'])) {
            $first_name = $_POST['first_name'];
            $username = str_replace(' ', '_', $first_name);
        }
        if (isset($_POST['last_name']) && !empty($_POST['last_name'])) {
            $last_name = $_POST['last_name'];
            $last_name = str_replace(' ', '_', $last_name);
            $username =$username.'_'.$last_name;
        }
        $is_exist = Wo_IsNameExist($username, 0);
        if (in_array(true, $is_exist)) 
        {
            $six_digit_random_number = mt_rand(100000, 999999);
            $username = $username . '_'.$six_digit_random_number;
        }
        $username=str_replace(".", "", $username);
        
        $activate  = ($wo['config']['emailValidation'] == '1') ? 0 : 1;
        //$device_id = (!empty($_POST['device_id'])) ? $_POST['device_id'] : '';
        $gender = 'male';
        if (in_array($_POST['gender'], array_keys($wo['genders']))) {
            $gender = $_POST['gender'];
        }
        if (!empty($_POST['phone_num'])) {
            $phone_number = Wo_Secure($_POST['phone_num']);
        }
        $code = md5(rand(1111, 9999) . time());
		$fourRandomDigit = mt_rand(1000,9999);
        $account_data = array(
            'email' => Wo_Secure($email, 0),
            'username' => Wo_Secure($username, 0),
            'password' => $password,
            'email_code' => $code,
			'username' =>$username,
			'otp_code' =>$fourRandomDigit,
            'src' => 'Phone',
            'timezone' => 'UTC',
            'phone_number' => $phone_number,
            'gender' => Wo_Secure($gender),
            'lastseen' => time(),
            'profile_type' => Wo_Secure($profile_type),
            'last_name' => Wo_Secure($last_name),
            'first_name' => Wo_Secure($first_name),
            'active' => Wo_Secure($activate)
        );
        if (!empty($_POST['android_m_device_id'])) {
            $account_data['android_m_device_id']  = Wo_Secure($_POST['android_m_device_id']);
        }
        if (!empty($_POST['ios_m_device_id'])) {
            $account_data['ios_m_device_id']  = Wo_Secure($_POST['ios_m_device_id']);
        }
        if (!empty($_POST['android_n_device_id'])) {
            $account_data['android_n_device_id']  = Wo_Secure($_POST['android_n_device_id']);
        }
        if (!empty($_POST['ios_n_device_id'])) {
            $account_data['ios_n_device_id']  = Wo_Secure($_POST['ios_n_device_id']);
        }
        $register     = Wo_RegisterUser($account_data);
        if ($register === true) {
            if ($activate == 1) {
                $access_token        = sha1(rand(111111111, 999999999)) . md5(microtime()) . rand(11111111, 99999999) . md5(rand(5555, 9999));
                $time                = time();
                $user_id             = Wo_UserIdFromUsername($username);
                $create_access_token = mysqli_query($sqlConnect, "INSERT INTO " . T_APP_SESSIONS . " (`user_id`, `session_id`, `platform`, `time`) VALUES ('{$user_id}', '{$access_token}', 'phone', '{$time}')");
                if ($create_access_token) {
                    $response_data = array(
                        'api_status' => 200,
                        'access_token' => $access_token,
                        'user_id' => $user_id
                    );
                }
            } elseif ($wo['config']['sms_or_email'] == 'mail') {
                $user_id             = Wo_UserIdFromUsername($username);
                $wo['user']        = $_POST;
                $wo['code']        = $code;
				$wo['otp_code']    = $fourRandomDigit;
				$wo['username']    = $$username;
                //$body              = Wo_LoadPage('emails/activate');			
				$body              = Wo_LoadPage('emails/mobileactivate');
                $send_message_data = array(
                    'from_email' => $wo['config']['siteEmail'],
                    'from_name' => $wo['config']['siteName'],
                    'to_email' => $email,
                    'to_name' => $username,
                    'subject' => $wo['lang']['account_activation'],
                    'charSet' => 'utf-8',
                    'message_body' => $body,
                    'is_html' => true
                );
                $send              = Wo_SendMessage($send_message_data);
              // Always set content-type when sending HTML email
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				// More headers
              	$i = 1;
				$headers .= 'From: zothai <zothai10@gmail.com>' . "\r\n";
				mail($email,$wo['lang']['account_activation'],$body,$headers);
                //if ($send) {
              if($i == 1){
                    $response_data = array(
                        'api_status' => 220,
                        'message' => 'Registration successful! We have sent you an email, Please check your inbox/spam to verify your email.',
                        'user_id' => $user_id
                    );
                } else {
                    $error_code    = 11;
                    $error_message = 'Error found while sending the verification email, please try again later.';
                }
            }
            elseif ($wo['config']['sms_or_email'] == 'sms' && !empty($_POST['phone_num'])) {
                $random_activation = Wo_Secure(rand(11111, 99999));
                $message           = "Your confirmation code is: {$random_activation}";

                if (Wo_SendSMSMessage($_POST['phone_num'], $message) === true) {
                    $user_id             = Wo_UserIdFromUsername($username);
                    $query             = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `sms_code` = '{$random_activation}' WHERE `user_id` = {$user_id}");
                    $response_data = array(
                        'api_status' => 220,
                        'message' => 'Registration successful! We have sent you an sms, Please check your phone to verify your account.',
                        'user_id' => $user_id
                    );
                } else {
                    $error_code    = 11;
                    $error_message = 'Error found while sending the verification sms, please try again later.';
                }
            }
            elseif ($wo['config']['sms_or_email'] == 'sms' && empty($_POST['phone_num'])) {
                $error_code    = 12;
                $error_message = 'phone_num can not be empty.';
            }
        }
    }
}