<?php 
if ($f == 'friend_user' && $wo['loggedin'] === true) {
    if (isset($_GET['following_id']) && Wo_CheckMainSession($hash_id) === true) {
        $user_followers = Wo_CountFriends($wo['user']['id'], true);
        $friends_limit  = $wo['config']['connectivitySystemLimit'];
        if (Wo_IsFriends($_GET['following_id'], $wo['user']['user_id']) === true || Wo_IsFriendRequested($_GET['following_id'], $wo['user']['user_id']) === true) 
        {
            if (Wo_DeleteFriend($_GET['following_id'], $wo['user']['user_id'])) {
                $data = array(
                    'status' => 200,
                    'can_send' => 0,
                    'html' => ''
                );
            }
        } 
        /*else if ($wo['config']['connectivitySystem'] == 1 && $user_followers >= $friends_limit) 
        {
            $data = array(
                'status' => 400,
                'can_send' => 0
            );
        } */
        else 
        {
            if (Wo_RegisterFriends($_GET['following_id'], $wo['user']['user_id'])) {
                $data = array(
                    'status' => 200,
                    'can_send' => 0,
                    'html' => ''
                );
                if (Wo_CanSenEmails()) {
                    $data['can_send'] = 1;
                }
            }
        }
    }
    if ($wo['loggedin'] == true) {
        Wo_CleanCache();
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
