<?php 
if ($f == 'payment') 
{
    //echo "<pre>".'asdfaspdfp';print_r($_GET);die;
    if (!isset($_GET['success'], $_GET['paymentId'], $_GET['PayerID'])) {
		$deletePendingRequest = mysqli_query($sqlConnect, "DELETE FROM " . T_VERIFICATION_REQUESTS . " WHERE `user_id` = '{$wo['user']['user_id']}' AND `payment_status` = 0 ");
		
        header("Location: " . Wo_SeoLink('index.php?link1=oops'));
        exit();
    }
    $is_pro = 0;
    $user   = Wo_UserData($wo['user']['user_id']);
    $pro_type        = 0;
    if (!isset($_GET['pro_type'])) {
        header("Location: " . Wo_SeoLink('index.php?link1=oops'));
        exit();
    }
    $pro_type = $_GET['pro_type'];
    $payment  = Wo_CheckPayment($_GET['paymentId'], $_GET['PayerID']);
    if (is_array($payment)) {
        if (isset($payment['name'])) {
            if ($payment['name'] == 'PAYMENT_ALREADY_DONE' || $payment['name'] == 'MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED') {
                $is_pro = 1;
            }
        }
    } else if ($payment === true) {
        $is_pro = 1;
    }
   

    $time = time();
    if ($is_pro == 1) 
    {
        global $sqlConnect;
        
		if($_GET['payment_type'] == "package") {
			
			$selectData = mysqli_query($sqlConnect, "SELECT * FROM " . T_VERIFICATION_REQUESTS . " WHERE `id` = '{$pro_type}'");
			$fetched_data = mysqli_fetch_assoc($selectData);
			
			$plan_name = $fetched_data['plan_name'];
			
			$query_one = mysqli_query($sqlConnect, "UPDATE " . T_VERIFICATION_REQUESTS . " SET `payment_status` = '1' WHERE `id` = '{$pro_type}'");
			
			$date  = time();
			$amount = $fetched_data['plan_amount'];
			$type   = $fetched_data['plan_name'].' Month Package Purchase';
			$query = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENTS . " (`user_id`, `amount`, `date`, `type`) VALUES ({$wo['user']['user_id']}, {$amount}, '{$date}', '{$type}')");
			
			$amount1 = $amount;
			$notes = $type;
			$create_payment_log = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ({$wo['user']['user_id']}, {$pro_type}, {$amount1}, '{$notes}')");
			
			if($fetched_data['plan_name'] == 6) {
				$plan_start_date = date("Y-m-d");
				$plan_end_date = date("Y-m-d", strtotime("+6 months"));
			} else {
				$plan_start_date = date("Y-m-d");
				$plan_end_date = date("Y-m-d", strtotime("+3 months"));
			}
			
			$updateUsers = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `plan_name` = '{$plan_name}', `plan_start_date` = '{$plan_start_date}', `plan_end_date` = '{$plan_end_date}' WHERE `user_id` = '{$wo['user']['user_id']}'");
			
			$deletePendingRequest = mysqli_query($sqlConnect, "DELETE FROM " . T_VERIFICATION_REQUESTS . " WHERE `user_id` = '{$wo['user']['user_id']}' AND `payment_status` = 0 ");
			
			header("Location: " . Wo_SeoLink('index.php?link1=group-board&payment=success&payment_type=package'));
			exit();
			
		} else {
			$amount1 = '30.00';
			$notes              = 'Move to group Board';
			$create_payment_log = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ({$wo['user']['user_id']}, {$pro_type}, {$amount1}, '{$notes}')");
			$query_one = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `moved_to_post_board` = '1',`moved_date`=CURRENT_DATE() WHERE `id` = '{$pro_type}'");
			$create_payment     = Wo_CreatePayment($amount1);
			// if ($mysqli) 
			// {
				//record affiliate with fixed price 
				 header("Location: " . Wo_SeoLink('index.php?link1=group-board&payment=success'));
					exit();
			//}
		}
    } 
    else 
    {
		$deletePendingRequest = mysqli_query($sqlConnect, "DELETE FROM " . T_VERIFICATION_REQUESTS . " WHERE `user_id` = '{$wo['user']['user_id']}' AND `payment_status` = 0 ");
		
        header("Location: " . Wo_SeoLink('index.php?link1=oops'));
        exit();
    }
    
}
