<?php

$messages = Wo_GetMessagesUsers($wo['user']['user_id'], '', 5);
$groups_messages = Wo_GetGroupsListAPP(array('limit' => 5));

$array = array();
if (!empty($messages)) {
	foreach ($messages as $key => $value) {
		$array[] = $value;
	}
}

 if (!empty($groups_messages)) {
	foreach ($groups_messages as $key => $value) {
		$array[] = $value;
	}
}

/* echo "<pre>";
print_r($messages);
die; */
$messageArray = array();

array_multisort( array_column($array, "chat_time"), SORT_DESC, $array );
if (!empty($array)) {		
 
	$array_count = 0;
	foreach ($array as $key => $value) 
	{
		if ($array_count < 5) 
		{
			if (!empty($value['group_id']) && !empty($value['last_message'])) 
			{	
				$messages = Wo_GetGroupMessagesAPP(array('group_id' => $value['group_id'], 'limit' => 1));
				$message = array();
				$message = $messages[0];
				
				$unread_class = 'read';
				if (!empty($message) && isset($message['time']) && isset($message['from_id']) && $message['from_id'] != $wo['user']['id']) {
					if ($message['time'] > $wo['user']['lastseen']) {
						$unread_class = ' unread';
					}
				}
				   
				$tmp_array = array(
					'id' => $value['group_id'],
					'name' => $value['group_name'],
					'message' => $message['orginal_text'],
					'time' => (!empty($message['time'])) ? Wo_Time_Elapsed_String($message['time']) : '',
					'seen_status' => $unread_class,
					'image' => $value['avatar'],
					'type' => 'group' 
				);
				
				array_push($messageArray, $tmp_array);
			}
			elseif (!empty($value['message']['page_id']) && $value['message']['page_id'] > 0) 
			{
				$wo['page_message'] = array();
				$message = Wo_GetPageMessages(array(
										'page_id' => $value['message']['page_id'],
										'from_id' => $value['message']['user_id'],
										'to_id'   => $value['message']['conversation_user_id'],
										'limit' => 1,
										'limit_type' => 1
									));
				$wo['page_message']['message'] = $message[0];
				
				$unread_class = 'read';
				if (!empty($wo['page_message']['message']) && isset($wo['page_message']['message']['time']) && isset($wo['page_message']['message']['from_id']) && $wo['page_message']['message']['from_id'] != $wo['user']['id']) {
					if ($wo['page_message']['message']['time'] > $wo['user']['lastseen']) {
						$unread_class = ' unread';
					}
				}
				
				$page_info = Wo_PageData($wo['page_message']['message']['page_id']);
				
				$avatar = $page_info['avatar'];
				$name = $page_info['page_name'];
				
				if ($page_info['is_page_onwer'] == 1) {
					if ($page_info['user_id'] != $wo['page_message']['message']['from_id']) {
						$user_info = Wo_UserData($wo['page_message']['message']['from_id']);
					}
					else{
						$user_info = Wo_UserData($wo['page_message']['message']['to_id']);
					}
				   
					$avatar = $user_info['avatar'];
					$name = $user_info['name'].' ('.$page_info['page_name'].')';
				}
				
				$tmp_array = array(
					'id' => $wo['page_message']['message']['page_id'],
					'name' => $name,
					'message' => $wo['page_message']['message']['text'],
					'time' => (!empty($wo['page_message']['message']['time'])) ? Wo_Time_Elapsed_String($wo['page_message']['message']['time']) : '',
					'seen_status' => $unread_class,
					'image' => $avatar,
					'type' => 'page' 
				);
				
				array_push($messageArray, $tmp_array);
			}
			else
			{
				$message = Wo_GetMessagesHeader(array('user_id' => $value['user_id']), 'user');
				if (!empty($message['messageUser'])) 
				{
					$wo['message'] = $value;
					
					$unread_class = 'read';
					if (!empty($message['from_id'])) {
						if ($message['seen'] == 0 && $wo['message']['user_id'] == $message['from_id']) {
							$unread_class = ' unread';
						}
					}

					$tmp_array = array(
						'id' => $wo['message']['user_id'],
						'name' => ($wo['message']['name']) ? $wo['message']['name'] : "",
						'message' => $message['text'],
						'time' => (!empty($message['time'])) ? Wo_Time_Elapsed_String($message['time']) : '',
						'seen_status' => $unread_class,
						'image' => $wo['message']['avatar'],
						'type' => 'user' 
					);
					
					array_push($messageArray, $tmp_array);
				}
			}	
			
			$array_count = $array_count + 1;
		}
	}		
}

$response_data = array(
	'api_status' => 200,
	'data' => $messageArray
);
