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
$options['offset'] = (!empty($_POST['current_index'])) ? (int) $_POST['current_index'] : 0;
$options['category_id'] = (!empty($_POST['category_id'])) ? (int) $_POST['category_id'] : 180;
$options['location_id'] = (!empty($_POST['location_id'])) ? (int) $_POST['location_id'] : 7;

$options['postType'] = (!empty($_POST['postType'])) ?  $_POST['postType'] : 'group';
$options['api'] = true;
//echo "<pre>";print_r($options);die;
 $stories['post_board'] = Wo_GetMovedPosts($options);
 $stories['post_something'] = Wo_GetGroupPosts($options);

//echo "<pre>";print_r($stories);die;

$response_data = array(
    'api_status' => 200,
    'stories' => $stories
);