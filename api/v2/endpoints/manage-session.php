<?php
	$get_sessions=array();
	$logged_user_id = Wo_Secure($wo['user']['user_id']);
	if(!empty($logged_user_id))
	{
		$get_sessions = Wo_GetAllSessionsFromUserID($logged_user_id);
	}
 	

	$response_data = array(
    	'api_status' => 200,
    	'my_sessions' => $get_sessions
	);