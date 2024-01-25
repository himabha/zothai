<?php
	$data=array();
 	$data[] = Wo_GetMytransactions();

	$response_data = array(
    	'api_status' => 200,
    	'transactions' => $data
	);