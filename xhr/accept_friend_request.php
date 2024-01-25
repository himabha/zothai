<?php 
if ($f == 'accept_friend_request') {
    if (isset($_GET['following_id'])) {
        if (Wo_AcceptFriendRequest($_GET['following_id'], $wo['user']['user_id'])) {
            $data = array(
                'status' => 200,
                'html' => Wo_GetFriendButton($_GET['following_id'])
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
