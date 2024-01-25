<?php

if (!empty($_POST['id']) && !empty($_POST['parent_id'])) {
    
	$id = $_POST['id'];
	$parent_id = $_POST['parent_id'];
	
	$query   = mysqli_query($sqlConnect, "SELECT * FROM " . T_ALBUMS_MEDIA . " WHERE `id` = {$id} limit 1 ");
    $imageData = mysqli_fetch_assoc($query);
	
    if (!empty($imageData)) {
       
	   @unlink($imageData['image']);
	   
	   $query = mysqli_query($sqlConnect, "DELETE FROM " . T_ALBUMS_MEDIA . " WHERE `id` = {$id} ");
	   $query1 = mysqli_query($sqlConnect, "DELETE FROM " . T_POSTS . " WHERE `id` = {$parent_id} ");
	   $query2 = mysqli_query($sqlConnect, "DELETE FROM " . T_ALBUMS_MEDIA . " WHERE `post_id` = {$parent_id} ");
	   
	   $response_data = array(
			'api_status' => 200,
			'message' => 'image successfully deleted'
		);
    }
	else{
		$error_code    = 7;
		$error_message = 'Image not found';
	}
}
else{
	$error_code    = 6;
    $error_message = 'Please check your details.';
}