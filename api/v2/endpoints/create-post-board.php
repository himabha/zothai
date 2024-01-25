<?php

    if (empty($_POST['title'])) {
        $error = 'Title is required!!';
    } 
    else if(empty($_POST['about']))
    {
    	$error = 'Board Description is required!!';
    }

    if (empty($error)) 
    {
    	global $wo, $sqlConnect;
    	$title=Wo_Secure($_POST['title']);
    	$about=Wo_Secure($_POST['about']);
      $user_id = Wo_Secure($wo['user']['user_id']);
       
      $query_three = mysqli_query($sqlConnect, "INSERT INTO " . T_categories . " (`user_id`, `name`, `about_board`, `created_by`, `status`,`level`,`parent_id`) VALUES('{$user_id}', '{$title}', '{$about}', '2','0','2','268')");
        if ($query_three) 
        {
           $response_data = array(
                'message' => 'Post Board added successfully!!!',
                'api_status' => 200
            );
        }
       	else 
       	{
            $response_data = array(
                'api_status' => 500,
                'message' => $wo['lang']['please_check_details']
            );
        }
    } 
    else 
    {
        $response_data = array(
            'api_status' => 500,
            'message' => $error
        );
    }
