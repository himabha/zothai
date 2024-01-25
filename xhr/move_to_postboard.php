<?php
if ($f == "move_to_postboard"){
	$data      = array();
   	$request   = array();

	$request[] = (empty($_POST['post_id']) || empty($_POST['post_id']));

	$story_id = $_POST['post_id'];
	$user_id  = $wo['user']['user_id'];
	
    if (in_array(true, $request)) {
    	$error = $error_icon . $wo['lang']['please_check_details'];
    }
    if (empty($error)) {
    	$mysqli    = mysqli_query($sqlConnect, "SELECT id FROM " . T_POSTS . " WHERE `post_id` = '$story_id' LIMIT 0,1");
		$sql_fetch = mysqli_fetch_assoc($mysqli);
		
        if ($sql_fetch) {
			$updated_id = $sql_fetch['id'];
			$moved = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `moved_to_post_board` = '1', `moved_date` = '".date('Y-m-d H:i:s')."' WHERE `id` = '{$updated_id}'");
        	if($moved) {
				$data = array(
                    'message' => 'Post moved successfully',
                    'status' => 200
                );
        	}
		} else {
            $data = array(
                'status' => 500,
                'message' => 'Post not found !!'
            );
		}
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}