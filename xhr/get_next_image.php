<?php 
if ($f == 'get_next_image') {
    $html      = '';
    $postsData = array(
        'limit' => 1,
        'filter_by' => 'photos',
        'for_type' => 'previous',
        'order' => 'ASC',
        'before_post_id' => Wo_Secure($_GET['post_id'])
    );
    if (!empty($_GET['type']) && !empty($_GET['id'])) {
        if ($_GET['type'] == 'profile') {
            $postsData['publisher_id'] = $_GET['id'];
        } else if ($_GET['type'] == 'page') {
            $postsData['page_id'] = $_GET['id'];
        } else if ($_GET['type'] == 'group') {
            $postsData['group_id'] = $_GET['id'];
        }
    }
	
	$postId = $_GET['post_id'];
	$getPostId = Wo_checkNextPostMedia($postId, "next");
	
	if(!empty($getPostId)) {
		foreach (Wo_GetPostImageById($getPostId['post_id']) as $wo['story']) {
			if (empty($wo['story']['album_name']) && $wo['story']['multi_image'] != 1) {
				$html .= Wo_LoadPage('lightbox/content');
			}
		}
	}	
	
    $data = array(
        'status' => 200,
        'html' => $html
    );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
