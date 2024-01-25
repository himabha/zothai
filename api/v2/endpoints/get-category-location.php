<?php
$categories = array();


$options['api'] = true;
//echo "<pre>";print_r($options);die;
 	$categories = Wo_GetPostCategories($options);
 	$regions=Wo_GetLocations(1);
  $cities=Wo_GetLocations(2); 

//echo "<pre>";print_r($stories);die;

$response_data = array(
    'api_status' => 200,
    'categories' => $categories,
    'regions' => $regions,
    'cities' => $cities,
);