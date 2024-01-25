<?php
    $response_data      = array();
    $request   = array();

    $request[] = (empty($_POST['post_id']) || empty($_POST['post_id']));
    if (empty($_POST['post_id'])) 
    {
        $error_code    = 3;
        $error_message = 'post_id (POST) is missing';
    }
    $story_id = $_POST['post_id'];
    $user_id  = $wo['user']['user_id'];
    
    if (in_array(true, $request)) {
        $error = $wo['lang']['please_check_details'];
    }
     $response_data = array(
                'api_status' => 500,
                'message' => 'Post not found !!'
            );
    if (empty($error)) 
    {

        
        if (!empty($_POST['payment_info'])) 
        {
            $amount1 = '30.00';
            $notes              = 'Move to group Board';
            $payment_info=json_decode($_POST['payment_info']);

            //echo "<pre>";print_r($payment_info->response->id);die;
            $payment_id=$payment_info->response->id;
            $create_payment_log = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`,`platform`,`payment_id`) VALUES ({$wo['user']['user_id']}, {$story_id}, {$amount1}, '{$notes}','mobile','{$payment_id}')");
            $query_one = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `moved_to_post_board` = '1',`moved_date`=CURRENT_DATE() WHERE `id` = '{$story_id}'");
            $create_payment     = Wo_CreatePayment($amount1);


            $response_data = array(
                    'message' => 'Post moved successfully',
                    'api_status' => 200
                );
        }
        else
        {
            $response_data = array(
                        'api_status' => 500,
                        'message' => 'Payment data not found !!'
                );
        }


       

    }
 