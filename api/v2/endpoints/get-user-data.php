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
if (empty($_POST['fetch'])) {
    $error_code    = 3;
    $error_message = 'fetch (POST) is missing';
}
if (empty($error_code)) {
    $recipient_id   = Wo_Secure($_POST['user_id']);
    $logged_user_id  = $wo['user']['user_id'];
    $recipient_data = Wo_UserData($recipient_id);
    if (empty($recipient_data)) {
        $error_code    = 6;
        $error_message = 'Recipient user not found';
    } else {
    	$response_data = array('api_status' => 200);
		$recipient_data_ = Wo_UpdateUserDetails($recipient_data, true, true, true);
        if (is_array($recipient_data_)) {
            $recipient_data = $recipient_data_;
        }
      	
      	$recipient_setting = (Array) json_decode(html_entity_decode($recipient_data['notification_settings']));
      
        foreach ($non_allowed as $key => $value) {
           unset($recipient_data[$value]);
        }
	    $fetch = explode(',', $_POST['fetch']);
		$data = array();
		foreach ($fetch as $key => $value) {
			$data[$value] = $value;
		}
		if (!empty($data['user_data'])) {
          
          	if($recipient_id != $logged_user_id && $recipient_setting['e_visited'] == 1) {
				
				$notification_data_array = array(
					'recipient_id' => $recipient_id,
					'type' => 'visited_profile',
					'url' => 'index.php?link1=timeline&u=' . $wo['user']['username']
				);
				Wo_RegisterNotification($notification_data_array);
			}
          
			$recipient_data['is_following'] = 0;

	        $recipient_data['can_follow'] = 0;
	        $recipient_data['is_friend'] = 0;
	        if (Wo_IsFollowing($recipient_id, $logged_user_id)) {
	            $recipient_data['is_following'] = 1;
	            $recipient_data['can_follow'] = 1;
	        } else {
	            if (Wo_IsFollowRequested($recipient_id, $logged_user_id)) {
	                $recipient_data['is_following'] = 2;
	                $recipient_data['can_follow'] = 1;
	            } else {
	                if ($recipient_data['follow_privacy'] == 1) {
	                    if (Wo_IsFollowing($logged_user_id, $recipient_id)) {
	                        $recipient_data['is_following'] = 0;
	                        $recipient_data['can_follow'] = 1;
	                    }
	                } else if ($recipient_data['follow_privacy'] == 0) {
	                    $recipient_data['can_follow'] = 1;
	                }
	            }
	        }
	        if (Wo_IsFriends($recipient_id, $logged_user_id)) {
	            $recipient_data['is_friend'] = 1;
	            
	        }
	        else 
	        {
	            if (Wo_IsFriendRequested($recipient_id, $logged_user_id)) 
	            {
	                $recipient_data['is_friend'] = 2;
	            } 
	            else if (Wo_IsFriendRequested($logged_user_id, $recipient_id)) 
		        {
		            $recipient_data['is_friend'] = 3;
		        } 
	        }


	        $recipient_data['is_following_me'] = (Wo_IsFollowing( $wo['user']['user_id'], $recipient_data['user_id'])) ? 1 : 0;
	        $recipient_data['gender_text']        = ($recipient_data['gender'] == 'male') ? $wo['lang']['male'] : $wo['lang']['female'];
        	$recipient_data['lastseen_time_text'] = Wo_Time_Elapsed_String($recipient_data['lastseen']);
        	$recipient_data['is_blocked']         = Wo_IsBlocked($recipient_data['user_id']);
        	$response_data['user_data'] = $recipient_data;
		}

		if (!empty($data['followers'])) {
			$followers_latest = array();
			$followers = Wo_GetFollowers($recipient_data['user_id'], 'profile', 50);
			foreach ($followers as $key => $follower) {
				$follower['is_following'] = (Wo_IsFollowing($follower['user_id'], $wo['user']['user_id'])) ? 1 : 0;
				$followers_latest[] = $follower;
			}
			$response_data['followers'] = $followers_latest;
		}
		if (!empty($data['following'])) {
			$followings_latest = array();
			$followings = Wo_GetFollowing($recipient_data['user_id'], 'profile', 50);
			foreach ($followings as $key => $following) {
				$following['is_following'] = (Wo_IsFollowing($following['user_id'], $wo['user']['user_id'])) ? 1 : 0;
				$followings_latest[] = $following;
			}
			$response_data['following'] = $followings_latest;
		}
		if (!empty($data['liked_pages'])) {
			$response_data['liked_pages'] = Wo_GetLikes($recipient_data['user_id'], 'profile', 50);
			foreach ($response_data['liked_pages'] as $key => $value) {
                $response_data['liked_pages'][$key]['is_liked'] = Wo_IsPageLiked($value['page_id'], $wo['user']['id']);
            }
		}
		if (!empty($data['liked_posts'])) {
        	//$response_data['post_liked_users'] = Wo_SecureData(array('multi_array' => true), Wo_GetPostLikes($post_id));
             $react_array = array('like' => 'Like','love' => 'Love' ,'haha' => 'HaHa' ,'wow' => 'WoW' ,'sad' => 'Sad' ,'angry' => 'Angry' );

             $response_data['liked_posts'] = Wo_GetPostsLikedUsers($recipient_id ,100,'0','user');
             //echo "<pre>";print_r($response_data['liked_posts']);die;
        }
		
		if (!empty($data['joined_groups'])) {
			$response_data['joined_groups'] = Wo_GetUsersGroups($recipient_data['user_id'], 50);
			foreach ($response_data['joined_groups'] as $key => $value) {
                $response_data['joined_groups'][$key]['is_joined'] = Wo_IsGroupJoined($value['group_id'], $wo['user']['id']);
            }
		}
		if (!empty($data['family'])) {
			$family = Wo_GetFamaly($recipient_data['user_id'],false,1);
			foreach ($family as $key => $value) {
				foreach ($non_allowed as $key1 => $value) {
			       unset($family[$key]['user_data'][$value]);
			    }
			}
			$response_data['family'] = $family;
		}
    }
}