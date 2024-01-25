<?php 
$response_data   = array(
    'api_status' => 400
);

    if (isset($_POST['user_id'])) 
    {
        $Userdata = Wo_UserData($_POST['user_id']);
        if (!empty($Userdata['user_id'])) 
        {
          
            if (isset($_POST['first_name']) && !empty($_POST['first_name'])) 
            {
                $first_name = $_POST['first_name'];
                $username=$first_name;
            }
            if (isset($_POST['last_name']) && !empty($_POST['last_name'])) 
            {
                $last_name = $_POST['last_name'];
            }
           
            $Update_data = array(
                'last_name' => Wo_Secure($last_name),
                'first_name' => Wo_Secure($first_name),
                'about' => $_POST['about'],
                'contact_detail'=>$_POST['contact_detail']
            );
            
            if (Wo_UpdateUserData($_POST['user_id'], $Update_data)) 
            {
                // echo "<pre>";print_r($_POST);die;
                $response_data = array(
                        'api_status' => 200,
                        //'first_name' => Wo_Secure($_POST['first_name']),
                        //'last_name' => Wo_Secure($_POST['last_name']),
                        'message' => $wo['lang']['setting_updated']
                    );
            }
           
        }
    }
