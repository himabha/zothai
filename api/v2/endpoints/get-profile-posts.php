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
$stories = array();

$options['limit'] = (!empty($_POST['limit'])) ? (int) $_POST['limit'] : 35;
$options['publisher_id'] = (!empty($_POST['publisher_id'])) ? (int) $_POST['publisher_id'] : $wo['user']['user_id'];

$options['postType'] = (!empty($_POST['postType'])) ?  $_POST['postType'] : '';
$options['api'] = true;
//echo "<pre>";print_r($options);die;
if($options['postType'] !='feed')
{
	$stories['post_board'] = Wo_GetMovedPosts($options);	
}
else
{
	$options['publisher_id']=0;
}
 
 $options['filter_by'] = 'postType';

 $stories['post_something'] = Wo_GetPostsDG($options);

//echo "<pre>";print_r($stories);die;

$response_data = array(
    'api_status' => 200,
    'stories' => $stories
);