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
    'api_status' => 400
);

$post_type = array("home_post", "blog", "events", "shop", "portfolio", "gallery");
if($_POST['type'] == "") // home_post, blog, events, shop, portfolio
{
	$error_code    = 5;
    $error_message = 'Type can not be empty';	
}
else if(!in_array($_POST['type'],$post_type))
{
	$error_code    = 5;
    $error_message = 'Type should be (home_post, blog, events, shop, portfolio, gallery)';	
}
else if($_POST['post_id'] == "")
{
	$error_code    = 5;
    $error_message = 'Post id can not be empty';	
} 
else if($_POST['user_id'] == "")
{
	$error_code    = 5;
    $error_message = 'User id can not be empty';	
} 
else 
{	
	$post = Wo_PostData(Wo_Secure($_POST['post_id']));
	$user = Wo_UserData(Wo_Secure($_POST['user_id']));
	
    if (!empty($post) && !empty($user)) 
	{
		if($_POST['type'] == "gallery") {
			$_POST['type'] = "photo";
		} 
		
        $result = Wo_SharePostOn($post['id'], $_POST['user_id'], $_POST['type']);
		
		if (!empty($_POST['text'])) {
			$updatePost = Wo_UpdatePost(array(
				'post_id' => $result,
				'text' => $_POST['text']
			));
		}
		
		$new_post = Wo_PostData($result);
		
		$notification_data_array = array(
			'recipient_id' => $post['user_id'],
			'post_id' => $post['id'],
			'type' => 'shared_your_post',
			'url' => 'index.php?link1=post&id=' . $result
		);
		Wo_RegisterNotification($notification_data_array);

		$response_data = array(
			'api_status' => 200,
			'data' => $new_post
		);
    }
    else
	{
		$error_code    = 5;
		$error_message = 'id and user_id can not be empty';
	}
}
