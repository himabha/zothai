<?php

$response_data = array(
    'api_status' => 400
);

if($update_last_seen = Wo_LastSeen($wo['user']['user_id'])) {
		$response_data = array(
			'api_status' => 200,
			'data' => "Last seen update successfully."
		);
} else {
    $error_code    = 4;
    $error_message = 'There is a someting went wrong.';
}