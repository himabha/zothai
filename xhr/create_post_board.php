<?php
//echo "<pre>";print_r($_POST);die;
if ($f == "create_post_board") 
{
    if (empty($_POST['title'])) {
        $error = $error_icon . 'Title is required!!';
    } 
    else if(empty($_POST['about']))
    {
    	$error = $error_icon . 'Board Description is required!!';
    }

    if (empty($error)) 
    {
    	global $wo, $sqlConnect;
    	$title=Wo_Secure($_POST['title']);
    	$about=Wo_Secure($_POST['about']);
      $user_id = Wo_Secure($wo['user']['user_id']);
	  $creator_name=Wo_Secure($_POST['creator_name']);
	 
       
      $query_three = mysqli_query($sqlConnect, "INSERT INTO " . T_categories . " (`user_id`,`creator_name`, `name`, `en_name`, `about_board`, `created_by`, `status`,`level`,`parent_id`) VALUES('{$user_id}','{$creator_name}', '{$title}', '{$title}', '{$about}', '2','0','2','268')");
        if ($query_three) 
        {
           $data = array(
                'message' => $success_icon .'Post Board submitted successfully!!!',
                'status' => 200
            );
        }
       	else 
       	{
            $data = array(
                'status' => 500,
                'message' => $wo['lang']['please_check_details']
            );
        }
    } 
    else 
    {
        $data = array(
            'status' => 500,
            'message' => $error
        );
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();

}