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
if (empty($error_code)) {
    $recipient_id   = Wo_Secure($_POST['user_id']);
    $recipient_data = Wo_UserData($recipient_id);
    if (empty($recipient_data)) {
        $error_code    = 6;
        $error_message = 'Recipient user not found';
    } 
    else 
    {
    	$follow_message = 'invalid';
        if (Wo_IsFriends($recipient_id, $wo['user']['user_id']) === true || Wo_IsFriendRequested($recipient_id, $wo['user']['user_id']) === true) 
        {
            if (Wo_DeleteFriend($recipient_id, $wo['user']['user_id'])) 
            {
                $follow_message = 'unfriend';
            }
        } 
        else 
        {
            if (Wo_RegisterFriends($recipient_id, $wo['user']['user_id'])) 
            {
                $follow_message = 'friend request sent';
            }
        }
        $response_data = array(
		    'api_status' => 200,
		    'follow_status' => $follow_message
		);
    }
}