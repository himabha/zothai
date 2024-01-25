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
if (empty($_POST['user_id'])) {
    $error_code    = 3;
    $error_message = 'user_id (POST) is missing';
}
if (empty($error_code))
{
	//echo $wo['user_profile']['user_id'];die;
	$user_id   = Wo_Secure($_POST['user_id']);
    $friends=Wo_GetFriends($user_id,'profile',100);
    $response_data = array(
		    'api_status' => 200,
		    'friends' => $friends
		);
}
