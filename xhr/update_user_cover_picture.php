<?php 
if ($f == 'update_user_cover_picture') 
{
    if (isset($_FILES['cover']['name'])) 
	{
        $type       = 'cover';
		$thumb_type = 'cover_thumb';
        if(isset($_POST['cover_id']) && !empty($_POST['cover_id']) && $_POST['cover_id'] !='1')
        {
            $type       = 'cover_'.$_POST['cover_id'];
			$thumb_type = 'cover_thumb_'.$_POST['cover_id'];
        }
        if (Wo_UploadProfileCoversDG($_FILES["cover"]["tmp_name"], $_FILES['cover']['name'], $type, $_FILES['cover']['type'], $_POST['user_id'],'',$_FILES["video_thumbnail"],$thumb_type) === true) 
		{
            $img              = Wo_UserData($_POST['user_id']);
            $_SESSION['file'] = $img['cover_org'];
            $data             = array(
                'status' => 200,
                'img' => $img[$type],
                // 'cover_or' => $img['cover_org'],
                // 'cover_full' => Wo_GetMedia($img['cover_full']),
                'session' => $_SESSION['file'],
                'cover_id'=>$_POST['cover_id']

            );
        } else {
			$data             = array(
                'status' => 401
            );
		}
    }
    Wo_CleanCache();
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}