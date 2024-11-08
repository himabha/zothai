<?php 
if ($f == 'register') {
    if (!empty($_SESSION['user_id'])) {
        $_SESSION['user_id'] = '';
        unset($_SESSION['user_id']);
    }
    if (!empty($_COOKIE['user_id'])) {
        $_COOKIE['user_id'] = '';
        unset($_COOKIE['user_id']);
        setcookie('user_id', null, -1);
        setcookie('user_id', null, -1,'/');
    }
    $fields = Wo_GetWelcomeFileds();
    if (empty($_POST['email'])  || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['profile_type'])) {
        $errors = $error_icon . $wo['lang']['please_check_details'];
    } else {
        //$is_exist = Wo_IsNameExist($_POST['username'], 0);
        if (empty($_POST['phone_num']) && $wo['config']['sms_or_email'] == 'sms') {
            $errors = $error_icon . $wo['lang']['worng_phone_number'];
        }
        // if (in_array(true, $is_exist)) {
        //     $errors = $error_icon . $wo['lang']['username_exists'];
        // }
        // if (Wo_IsBanned($_POST['username'])) {
        //     $errors = $error_icon . $wo['lang']['username_is_banned'];
        // }
        if (Wo_IsBanned($_POST['email'])) {
            $errors = $error_icon . $wo['lang']['email_is_banned'];
        }
        if (preg_match_all('~@(.*?)(.*)~', $_POST['email'], $matches) && !empty($matches[2]) && !empty($matches[2][0]) && Wo_IsBanned($matches[2][0])) {
            $errors = $error_icon . $wo['lang']['email_provider_banned'];
        }
        if (Wo_CheckIfUserCanRegister($wo['config']['user_limit']) === false) {
            $errors = $error_icon . $wo['lang']['limit_exceeded'];
        }
        // if (in_array($_POST['username'], $wo['site_pages'])) {
        //     $errors = $error_icon . $wo['lang']['username_invalid_characters'];
        // }
        // if (strlen($_POST['username']) < 5 OR strlen($_POST['username']) > 32) {
        //     $errors = $error_icon . $wo['lang']['username_characters_length'];
        // }
        // if (!preg_match('/^[\w]+$/', $_POST['username'])) {
        //     $errors = $error_icon . $wo['lang']['username_invalid_characters'];
        // }
        if (!empty($_POST['phone_num'])) {
            if (!preg_match('/^\+?\d+$/', $_POST['phone_num'])) {
                $errors = $error_icon . $wo['lang']['worng_phone_number'];
            } else {
                if (Wo_PhoneExists($_POST['phone_num']) === true) {
                    $errors = $error_icon . $wo['lang']['phone_already_used'];
                }
            }
        }
        if (Wo_EmailExists($_POST['email']) === true) {
            $errors = $error_icon . $wo['lang']['email_exists'];
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors = $error_icon . $wo['lang']['email_invalid_characters'];
        }
        if (strlen($_POST['password']) < 6) {
            $errors = $error_icon . $wo['lang']['password_short'];
        }
        if ($_POST['password'] != $_POST['confirm_password']) {
            $errors = $error_icon . $wo['lang']['password_mismatch'];
        }
        if ($config['reCaptcha'] == 1) {
            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                $errors = $error_icon . $wo['lang']['reCaptcha_error'];
            }
        }
        $gender = 'male';
        if (in_array($_POST['gender'], array_keys($wo['genders']))) {
            $gender = $_POST['gender'];
        }
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
        // if (!empty($_POST['gender'])) {
        //     if ($_POST['gender'] != 'male' && $_POST['gender'] != 'female') {
        //         $gender = 'male';
        //     } else {
        //         $gender = $_POST['gender'];
        //     }
        // }
        if (!empty($fields) && count($fields) > 0) {
            foreach ($fields as $key => $field) {
                if (empty($_POST[$field['fid']])) {
                    $errors = $error_icon . $field['name'] . ' is required';
                }
                if (mb_strlen($_POST[$field['fid']]) > $field['length']) {
                    $errors = $error_icon . $field['name'] . ' field max characters is ' . $field['length'];
                }
            }
        }
    }
    $field_data = array();
    if (empty($errors)) {
        if (!empty($fields) && count($fields) > 0) {
            foreach ($fields as $key => $field) {
                if (!empty($_POST[$field['fid']])) {
                    $name = $field['fid'];
                    if (!empty($_POST[$name])) {
                        $field_data[] = array(
                            $name => $_POST[$name]
                        );
                    }
                }
            }
        }
        $activate = ($wo['config']['emailValidation'] == '1') ? '0' : '1';
        $code = md5(rand(1111, 9999) . time());
        $re_data  = array(
            'email' => Wo_Secure($_POST['email'], 0),
            'username' => Wo_Secure($username),
            'password' => $_POST['password'],
            'email_code' => Wo_Secure($code, 0),
            'src' => 'site',
            'gender' => Wo_Secure($gender),
            'profile_type' => Wo_Secure($profile_type),
            'last_name' => Wo_Secure($last_name),
            'first_name' => Wo_Secure($first_name),
            'lastseen' => time(),
            'active' => Wo_Secure($activate),
            'birthday' => '0000-00-00'
        );
        if (!empty($_SESSION['ref']) && $wo['config']['affiliate_type'] == 0) {
            $ref_user_id = Wo_UserIdFromUsername($_SESSION['ref']);
            if (!empty($ref_user_id) && is_numeric($ref_user_id)) {
                $re_data['referrer'] = Wo_Secure($ref_user_id);
                $re_data['src']      = Wo_Secure('Referrer');
                $update_balance      = Wo_UpdateBalance($ref_user_id, $wo['config']['amount_ref']);
                unset($_SESSION['ref']);
            }
        }
        elseif (!empty($_SESSION['ref']) && $wo['config']['affiliate_type'] == 1) {
            $ref_user_id = Wo_UserIdFromUsername($_SESSION['ref']);
            if (!empty($ref_user_id) && is_numeric($ref_user_id)) {
                $re_data['ref_user_id']      = Wo_Secure($ref_user_id);
            }
        }
        if (!empty($_POST['phone_num'])) {
            $re_data['phone_number'] = Wo_Secure($_POST['phone_num']);
        }
        $in_code  = (isset($_POST['invited'])) ? Wo_Secure($_POST['invited']) : false;
        if (!empty($_POST['phone_num'])) {
            $register = Wo_RegisterUser($re_data, $in_code);
        }
        else{
            if($activate == 1){
               $register = Wo_RegisterUser($re_data, $in_code);
            }
            else{
                $register = true;
            }
        }
        
        if ($register === true) {
            if ($activate == 1) {
                $data  = array(
                    'status' => 200,
                    'message' => $success_icon . $wo['lang']['successfully_joined_label']
                );
                $login = Wo_Login($_POST['username'], $_POST['password']);
                if ($login === true) {
                    $session             = Wo_CreateLoginSession(Wo_UserIdFromUsername($_POST['username']));
                    $_SESSION['user_id'] = $session;
                    setcookie("user_id", $session, time() + (10 * 365 * 24 * 60 * 60));
                }
                if (!empty($wo['config']['auto_friend_users'])) {
                    $autoFollow = Wo_AutoFollow(Wo_UserIdFromUsername($_POST['username']));
                }
                if (!empty($wo['config']['auto_page_like'])) {
                    Wo_AutoPageLike(Wo_UserIdFromUsername($_POST['username']));
                }
                if (!empty($wo['config']['auto_group_join'])) {
                    Wo_AutoGroupJoin(Wo_UserIdFromUsername($_POST['username']));
                }
                $data['location'] = Wo_SeoLink('index.php?link1=start-up');
            } else if ($wo['config']['sms_or_email'] == 'mail') {
                $wo['user']        = $_POST;
                $wo['code']        = $code;
                $body              = Wo_LoadPage('emails/activate');
                $send_message_data = array(
                    'from_email' => $wo['config']['siteEmail'],
                    'from_name' => $wo['config']['siteName'],
                    'to_email' => $_POST['email'],
                    'to_name' => $_POST['username'],
                    'subject' => 'การเปิดใช้งานบัญชี | Account Activation',
                    'charSet' => 'utf-8',
                    'message_body' => $body,
                    'is_html' => true
                );
                $send              = Wo_SendMessage($send_message_data);
              
              // Always set content-type when sending HTML email
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				// More headers
				$headers .= 'From: zothai <zothai10@gmail.com>' . "\r\n";
				mail($_POST['email'],'การเปิดใช้งานบัญชี | Account Activation',$body,$headers);
                //$errors            = $success_icon . $wo['lang']['successfully_joined_verify_label'];
                 $data  = array(
                    'status' => 200,
                    'message' => $success_icon . $wo['lang']['successfully_joined_verify_label']
                );
            } else if ($wo['config']['sms_or_email'] == 'sms' && !empty($_POST['phone_num'])) {
                $random_activation = Wo_Secure(rand(11111, 99999));
                $message           = "Your confirmation code is: {$random_activation}";
                
                //if ($query) {
                    if (Wo_SendSMSMessage($_POST['phone_num'], $message) === true) {
                        $register = Wo_RegisterUser($re_data, $in_code);
                        $user_id           = Wo_UserIdFromUsername($_POST['username']);
                        $query             = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `sms_code` = '{$random_activation}' WHERE `user_id` = {$user_id}");
                        $data = array(
                            'status' => 300,
                            'location' => Wo_SeoLink('index.php?link1=confirm-sms?code=' . $code)
                        );
                    } else {
                        $errors = $error_icon . $wo['lang']['failed_to_send_code_email'];
                    }
                //}
            }
        }
        if (!empty($field_data)) {
            $user_id = Wo_UserIdFromUsername($_POST['username']);
            $insert  = Wo_UpdateUserCustomData($user_id, $field_data, false);
        }
    }
    header("Content-type: application/json");
    if (isset($errors)) {
        echo json_encode(array(
            'errors' => $errors
        ));
    } else {
        echo json_encode($data);
    }
    exit();
}
