<?php 
	$get_sessions=array();
	 if (!empty($_POST['id'])) {
        $id = Wo_Secure($_POST['id']);
    }
    $api_status=400;
    $message='Something went wrong. Please try again';
	$check_session = $db->where('id', $id)->getOne(T_APP_SESSIONS);
    if (!empty($check_session)) 
    {
        if (($check_session->user_id == $wo['user']['user_id']) || Wo_IsAdmin())
        {
            $delete_session = $db->where('id', $id)->delete(T_APP_SESSIONS);
            if ($delete_session) 
            {
                $api_status = 200;
                $message='Session deleted successfully';
            }
        }
    }
 	

	$response_data = array(
    	'api_status' => $api_status,
    	'message' => $message,
    );