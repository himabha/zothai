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
    'api_status' => 400
);

if(isset($_POST['type']) && $_POST['type']=='cover')
{ 
    if (isset($_FILES['cover']['name']) && !empty($_FILES['cover']['name'])) 
    {
        $type='cover';
		$videoThumb = "";
		$thumb_type = 'cover_thumb';
        if(isset($_POST['cover_id']) && !empty($_POST['cover_id']) && $_POST['cover_id'] !='1')
        {
            $type='cover_'.$_POST['cover_id'];
			$thumb_type = 'cover_thumb_'.$_POST['cover_id'];
        }

        if (Wo_UploadProfileCoversDG($_FILES["cover"]["tmp_name"], $_FILES['cover']['name'], $type, $_FILES['cover']['type'], $_POST['user_id'],'',$videoThumb,$thumb_type) === true) {
            $img              = Wo_UserData($_POST['user_id']);
            $_SESSION['file'] = $img['cover_org'];
			
			$imgres = str_replace($wo['config']['site_url']."/", "", $img[$type]); 
			
            $data             = array(
                'status' => 200,
                'img' => $imgres,
                'cover_id'=>$_POST['cover_id']

            );
            $response_data = array(
				'api_status' => 200,
				'data' => $data
			);
        }
    }
    else
    {
        $error_code    = 4;
        $error_message = 'Please Select image, it can not be empty';
    }
 
}
else if(isset($_POST['type']) && $_POST['type']=='video')
{ 
    if (isset($_FILES['video']['name']) && !empty($_FILES['video']['name'])) 
    {
        $type='cover';
		$thumb_type = 'cover_thumb';
        if(isset($_POST['cover_id']) && !empty($_POST['cover_id']) && $_POST['cover_id'] !='1')
        {
            $type='cover_'.$_POST['cover_id'];
			$thumb_type = 'cover_thumb_'.$_POST['cover_id'];
        }
		
		$videoThumb = "";
		
		if(isset($_FILES['cover']['name']) && !empty($_FILES['cover']['name'])) {
			$videoThumb = $_FILES["cover"];
		} else {
			$videoThumb = "";
		}
		
        if (Wo_UploadProfileCoversDG($_FILES["video"]["tmp_name"], $_FILES['video']['name'], $type, $_FILES['video']['type'], $_POST['user_id'],'',$videoThumb,$thumb_type) === true){
            $img              = Wo_UserData($_POST['user_id']);
            $_SESSION['file'] = $img['cover_org'];
			
			$imgres = str_replace($wo['config']['site_url']."/", "", $img[$type]); 
			
            $data             = array(
                'status' => 200,
                'img' => $img[$thumb_type],
                'video' => $imgres,
                'cover_id'=>$_POST['cover_id']

            );
            $response_data = array(
				'api_status' => 200,
				'data' => $data
			);
        }
    }
    else
    {
        $error_code    = 4;
        $error_message = 'Please Select video, it can not be empty';
    }
 
}
else if(isset($_POST['type']) && $_POST['type']=='caption')
{
    if(isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['title_id']) && !empty($_POST['title_id'])  )
   {
      global $wo, $sqlConnect;
        $arr=array();
        $arr['title']=$_POST['title'];
        $arr['link']=$_POST['link'];

        $title_id=$_POST['title_id'];
        $userid=$_POST['user_id'];
        $title= 'title_'.$title_id;
        $data=json_encode($arr);
        //$user_id              = Wo_Secure($user_id);
        $query        = "UPDATE " . T_USERS . " set `{$title}` = '{$data}' WHERE `user_id` = {$userid}";
        //echo $query;die;
        $sql_queryset = mysqli_query($sqlConnect, $query);
        if ($sql_queryset) 
        {
           $data = array(
                'title'=>$_POST['title'],
                'link'=>$_POST['link']
            );
            $response_data = array(
                                        'api_status' => 200,
                                        'data' => $data
                                    );
        }
    }
    else
    {
        $error_code    = 4;
        $error_message = 'POST data can not be empty';
    }
}
else
{
    $error_code    = 5;
    $error_message = 'Type can not be empty';
}
