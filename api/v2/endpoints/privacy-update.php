<?php 

$response_data = array(
    'api_status' => 400
);



if (!empty($_POST['postType'])) 
{
	
			$userId = $_POST['userId'];
			$friend_privacy = $_POST['friend_privacy'];
			$post_page_privacy = $_POST['post_page_privacy'];
			$for_yous_privacy = $_POST['for_yous_privacy'];
			$followers_privacy = $_POST['followers_privacy'];
			$birth_privacy = $_POST['birth_privacy'];
			$visit_privacy = $_POST['visit_privacy'];
			$status = $_POST['status'];
			$share_my_data = $_POST['share_my_data'];
			
             $add_timezone = mysqli_query($sqlConnect, "UPDATE Wo_Users SET `friend_privacy` = '{$friend_privacy}' ,`post_page_privacy` = '{$post_page_privacy}',`for_yous_privacy` = '{$for_yous_privacy}',`followers_privacy` = '{$followers_privacy}',`birth_privacy` = '{$birth_privacy}',`visit_privacy` = '{$visit_privacy}',`status` = '{$status}',`share_my_data` = '{$share_my_data}' WHERE `user_id` = {$userId}");
              $response_data = array(
                                    'api_status' => 200,
                                    'message' => 'Privacy Setting Update Succefully',
                                    'user_id' => $userId
                                );
               
           
     
        
        header("Content-type: application/json");
        echo json_encode($data);
        exit();



}