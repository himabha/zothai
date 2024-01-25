<?php 
if ($f == 'update_user_status') {

    
    // if ($wo['loggedin'] == false) {
    //     return false;
    // }
   //echo "<pre>";print_r($_POST);die;
    $data = array();
   if(isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['name']) && !empty($_POST['name'])  )
   {
    //echo "<pre>";print_r($_POST);die;
        $arr=array();
        $arr['title']=$_POST['title'];
        $arr['link']=$_POST['link'];

        $name=$_POST['name'];
        $userid=$_POST['user_id'];

       $data=json_encode($arr);
	   $da = '{"title":"'.$_POST['title'].'","link":"'.$_POST['link'].'"}';
	   //$data=($arr);
        //$user_id              = Wo_Secure($user_id);
        $query        = "UPDATE " . T_USERS . " set `{$name}` = '{$da}' WHERE `user_id` = {$userid}";
        //echo $query ;die;
        $sql_queryset = mysqli_query($sqlConnect, $query);
        if ($sql_queryset) {
           $data = array(
                'status' => 200
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
    
}
