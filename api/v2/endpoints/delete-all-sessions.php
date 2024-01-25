 <?php
 $delete_session = $db->where('user_id', $wo['user']['user_id'])->delete(T_APP_SESSIONS);
    if ($delete_session) {
        $data['status'] = 200;
    }

    $response_data = array(
    	'api_status' => 200,
    	'message' => 'All sessions deleted!!',
    );