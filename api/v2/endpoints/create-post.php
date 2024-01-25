<?php 

$response_data = array(
    'api_status' => 400
);

$required_fields =  array(
                        'postTitle',
                        'postText',
                        'location',
                        'category',
                        
                    );
foreach ($required_fields as $key => $value) {
    if (empty($_POST[$value]) && empty($error_code)) 
    {
        $error_code    = 3;
        $error_message = $value . ' (POST) is missing';
    }
}
//echo "<pre>";print_r($_POST);die;
if (!empty($_POST['postTitle']) && !empty($_POST['postText'])) 
{

	 			$media         = '';
        $mediaFilename = '';
        $post_photo    = '';
        $mediaName     = '';
        $video_thumb   = '';
        $html          = '';
        $recipient_id  = 0;
        $page_id       = 0;
        $group_id      = 0;
        $event_id      = 0;
        $invalid_file  = false;
        $errors        = false;
        $image_array   = array();
        $blur          = 0;
        // if (Wo_CheckSession($hash_id) === false) {
        //     return false;
        //     die();
        // }
        if (isset($_POST['recipient_id']) && !empty($_POST['recipient_id'])) {
            $recipient_id = Wo_Secure($_POST['recipient_id']);
        } else if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
            $event_id = Wo_Secure($_POST['event_id']);
        } else if (isset($_POST['page_id']) && !empty($_POST['page_id'])) {
            $page_id = Wo_Secure($_POST['page_id']);
        } else if (isset($_POST['group_id']) && !empty($_POST['group_id'])) {
            $group_id = Wo_Secure($_POST['group_id']);
            $group    = Wo_GroupData($group_id);
            if (!empty($group['id'])) {
                if ($group['privacy'] == 1) {
                    $_POST['postPrivacy'] = 0;
                } else if ($group['privacy'] == 2) {
                    $_POST['postPrivacy'] = 2;
                }
            }
        }
        if (isset($_FILES['postFile']['name'])) {
            if ($_FILES['postFile']['size'] > $wo['config']['maxUpload']) {
                $invalid_file = 1;
            } else if (Wo_IsFileAllowed($_FILES['postFile']['name']) == false) {
                $invalid_file = 2;
            } else {
                $fileInfo = array(
                    'file' => $_FILES["postFile"]["tmp_name"],
                    'name' => $_FILES['postFile']['name'],
                    'size' => $_FILES["postFile"]["size"],
                    'type' => $_FILES["postFile"]["type"]
                );
                $media    = Wo_ShareFile($fileInfo);
                if (!empty($media)) {
                    $mediaFilename = $media['filename'];
                    $mediaName     = $media['name'];
                }
            }
        }
        if (isset($_FILES['postVideo']['name']) && empty($mediaFilename)) {
            if ($_FILES['postVideo']['size'] > $wo['config']['maxUpload']) {
                $invalid_file = 1;
            } else if (Wo_IsFileAllowed($_FILES['postVideo']['name']) == false) {
                $invalid_file = 2;
            } else {
                $fileInfo = array(
                    'file' => $_FILES["postVideo"]["tmp_name"],
                    'name' => $_FILES['postVideo']['name'],
                    'size' => $_FILES["postVideo"]["size"],
                    'type' => $_FILES["postVideo"]["type"],
                    'types' => 'mp4,m4v,webm,flv,mov,mpeg'
                );
                $media    = Wo_ShareFile($fileInfo);
                if (!empty($media)) {
                    $mediaFilename = $media['filename'];
                    $mediaName     = $media['name'];
                    $img_types     = array(
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                        'image/gif'
                    );
                    if (!empty($_FILES['video_thumb']) && in_array($_FILES["video_thumb"]["type"], $img_types)) {
                        $fileInfo = array(
                            'file' => $_FILES["video_thumb"]["tmp_name"],
                            'name' => $_FILES['video_thumb']['name'],
                            'size' => $_FILES["video_thumb"]["size"],
                            'type' => $_FILES["video_thumb"]["type"],
                            'types' => 'jpeg,png,jpg,gif',
                            'crop' => array(
                                'width' => 525,
                                'height' => 295
                            )
                        );
                        $media    = Wo_ShareFile($fileInfo);
                        if (!empty($media)) {
                            $video_thumb = $media['filename'];
                        }
                    }
                }
            }
        }
        if (isset($_FILES['postMusic']['name']) && empty($mediaFilename)) {
            if ($_FILES['postMusic']['size'] > $wo['config']['maxUpload']) {
                $invalid_file = 1;
            } else if (Wo_IsFileAllowed($_FILES['postMusic']['name']) == false) {
                $invalid_file = 2;
            } else {
                $fileInfo = array(
                    'file' => $_FILES["postMusic"]["tmp_name"],
                    'name' => $_FILES['postMusic']['name'],
                    'size' => $_FILES["postMusic"]["size"],
                    'type' => $_FILES["postMusic"]["type"],
                    'types' => 'mp3,wav'
                );
                $media    = Wo_ShareFile($fileInfo);
                if (!empty($media)) {
                    $mediaFilename = $media['filename'];
                    $mediaName     = $media['name'];
                }
            }
        }
        $multi = 0;
        if (isset($_FILES['postPhotos']['name']) && empty($mediaFilename) && empty($_POST['album_name'])) {
            if (count($_FILES['postPhotos']['name']) == 1) {
                if ($_FILES['postPhotos']['size'][0] > $wo['config']['maxUpload']) {
                    $invalid_file = 1;
                } else if (Wo_IsFileAllowed($_FILES['postPhotos']['name'][0]) == false) {
                    $invalid_file = 2;
                } else {
                    
                        $fileInfo = array(
                            'file' => $_FILES["postPhotos"]["tmp_name"][0],
                            'name' => $_FILES['postPhotos']['name'][0],
                            'size' => $_FILES["postPhotos"]["size"][0],
                            'type' => $_FILES["postPhotos"]["type"][0]
                        );
                        $media    = Wo_ShareFile($fileInfo);
                        if (!empty($media)) {
                            $image_file = Wo_GetMedia($media['filename']);
                            $upload = true;
                            if ($wo['config']['adult_images'] == 1  && !detect_safe_search($image_file) && $wo['config']['adult_images_action'] == 1) {
                                $blur = 1;
                            }
                            elseif ($wo['config']['adult_images'] == 1  && detect_safe_search($image_file) == false && $wo['config']['adult_images_action'] == 0) {
                                $invalid_file = 3;
                                $upload = false;
                                @unlink($media['filename']);
                                Wo_DeleteFromToS3($media['filename']);
                            }
                            $mediaFilename = $media['filename'];
                            $mediaName     = $media['name'];
                        }
                }
            } else {
                $multi = 1;
            }
        }
        if (empty($_POST['postPrivacy'])) {
            $_POST['postPrivacy'] = 0;
        }
        $post_privacy  = 0;
        $privacy_array = array(
            '0',
            '1',
            '2',
            '3'
        );
        if (isset($_POST['postPrivacy'])) {
            if (in_array($_POST['postPrivacy'], $privacy_array)) {
                $post_privacy = $_POST['postPrivacy'];
            }
        }
        if (empty($page_id)) {
            setcookie("post_privacy", $post_privacy, time() + (10 * 365 * 24 * 60 * 60));
        }
        $import_url_image = '';
        $url_link         = '';
        $url_content      = '';
        $url_title        = '';
        if (!empty($_POST['url_link']) && !empty($_POST['url_title']) && filter_var($_POST['url_link'], FILTER_VALIDATE_URL)) {
            $url_link  = $_POST['url_link'];
            $url_title = $_POST['url_title'];
            if (!empty($_POST['url_content'])) {
                $url_content = $_POST['url_content'];
            }
            if (!empty($_POST['url_image'])) {
                $import_url_image = @Wo_ImportImageFromUrl($_POST['url_image']);
            }
        }
        $post_text = '';
        $post_title ='';
        $post_map  = '';
        $location  = '0';
        $category  = '0';
        if (!empty($_POST['postTitle']) && !ctype_space($_POST['postText'])) {
            $post_title = $_POST['postTitle'];
        }
        if (!empty($_POST['postText']) && !ctype_space($_POST['postText'])) {
            $post_text = $_POST['postText'];
        }
        if (!empty($_POST['postMap'])) {
            $post_map = $_POST['postMap'];
        }
        if (!empty($_POST['location'])) {
            $location = $_POST['location'];
        }
        if (!empty($_POST['category'])) {
            $category = $_POST['category'];
        }
        $album_name = '';
        if (!empty($_POST['album_name'])) {
            $album_name = $_POST['album_name'];
        }
        if (!isset($_FILES['postPhotos']['name'])) {
            $album_name = '';
        }
        $traveling = '';
        $watching  = '';
        $playing   = '';
        $listening = '';
        $feeling   = '';
        if (!empty($_POST['feeling_type'])) {
            $array_types = array(
                'feelings',
                'traveling',
                'watching',
                'playing',
                'listening'
            );
            if (in_array($_POST['feeling_type'], $array_types)) {
                if ($_POST['feeling_type'] == 'feelings') {
                    if (!empty($_POST['feeling'])) {
                        if (array_key_exists($_POST['feeling'], $wo['feelingIcons'])) {
                            $feeling = $_POST['feeling'];
                        }
                    }
                } else if ($_POST['feeling_type'] == 'traveling') {
                    if (!empty($_POST['feeling'])) {
                        $traveling = $_POST['feeling'];
                    }
                } else if ($_POST['feeling_type'] == 'watching') {
                    if (!empty($_POST['feeling'])) {
                        $watching = $_POST['feeling'];
                    }
                } else if ($_POST['feeling_type'] == 'playing') {
                    if (!empty($_POST['feeling'])) {
                        $playing = $_POST['feeling'];
                    }
                } else if ($_POST['feeling_type'] == 'listening') {
                    if (!empty($_POST['feeling'])) {
                        $listening = $_POST['feeling'];
                    }
                }
            }
        }
        if (isset($_FILES['postPhotos']['name'])) {
        	//echo "<pre>";print_r($_FILES);
            $allowed = array(
                'gif',
                'png',
                'jpg',
                'jpeg'
            );
            for ($i = 0; $i < count($_FILES['postPhotos']['name']); $i++) {
                $new_string = pathinfo($_FILES['postPhotos']['name'][$i]);
                //print_r($new_string);die;
                if (!in_array(strtolower($new_string['extension']), $allowed)) {
                    $errors[] = $wo['lang']['please_check_details'];
                }
                
            }
        }
        if (!empty($_POST['answer']) && array_filter($_POST['answer'])) {
            if (!empty($_POST['postText'])) {
                foreach ($_POST['answer'] as $key => $value) {
                    if (empty($value) || ctype_space($value)) {
                        $errors = 'Answer #' . ($key + 1) . ' is empty.';
                    }
                }
            } else {
                $errors = 'Please write the question.';
            }
        }
        if (empty($errors) && $invalid_file == false) 
        {
            $is_option = false;
            if (!empty($_POST['answer']) && array_filter($_POST['answer'])) {
                $is_option = true;
            }
            $post_active = 1;
            if ($wo['config']['post_approval'] == 1 && !Wo_IsAdmin()) {
                $post_active = 0;
            }
            $post_data = array(
                'user_id' => Wo_Secure($wo['user']['user_id']),
                // 'page_id' => Wo_Secure($page_id),
                // 'group_id' => Wo_Secure($group_id),
                // 'event_id' => Wo_Secure($event_id),
                'postTitle' => Wo_Secure($post_title),
                'postText' => Wo_Secure($post_text),

                'category_id' => Wo_Secure($category),
                'location_id' => Wo_Secure($location),

                'recipient_id' => Wo_Secure($recipient_id),
                'postRecord' => Wo_Secure($_POST['postRecord']),
                'postFile' => Wo_Secure($mediaFilename, 0),
                'postFileName' => Wo_Secure($mediaName),
                'postMap' => Wo_Secure($post_map),
                'postPrivacy' => Wo_Secure($post_privacy),
                'postLinkTitle' => Wo_Secure($url_title),
                'postLinkContent' => Wo_Secure($url_content),
                'postLink' => Wo_Secure($url_link),
                'postLinkImage' => Wo_Secure($import_url_image, 0),
                'album_name' => Wo_Secure($album_name),
                'multi_image' => Wo_Secure($multi),
                'postFeeling' => Wo_Secure($feeling),
                'postListening' => Wo_Secure($listening),
                'postPlaying' => Wo_Secure($playing),
                'postWatching' => Wo_Secure($watching),
                'postTraveling' => Wo_Secure($traveling),
                'postFileThumb' => Wo_Secure($video_thumb),
                'postType' => Wo_Secure($_POST['post_type']),
                'time' => time(),
                'blur' => $blur,
                'multi_image_post' => 0,
                'active' => $post_active
            );
            if (isset($_POST['postSticker']) && Wo_IsUrl($_POST['postSticker']) && empty($_FILES) && empty($_POST['postRecord'])) {
                $post_data['postSticker'] = $_POST['postSticker'];
            } else if (empty($_FILES['postPhotos']) && preg_match_all('/https?:\/\/(?:[^\s]+)\.(?:png|jpg|gif|jpeg)/', $post_data['postText'], $matches)) {
                if (!empty($matches[0][0]) && Wo_IsUrl($matches[0][0])) {
                    $post_data['postPhoto'] = @Wo_ImportImageFromUrl($matches[0][0]);
                }
            }
            if (!empty($is_option)) {
                $post_data['poll_id'] = 1;
            }
            if (!empty($_POST['post_color']) && !empty($post_text) && empty($_POST['postRecord']) && empty($mediaFilename) && empty($mediaName) && empty($post_map) && empty($url_title) && empty($url_content) && empty($url_link) && empty($import_url_image) && empty($album_name) && empty($multi) && empty($video_thumb) && empty($post_data['postPhoto'])) {
                $post_data['color_id'] = Wo_Secure($_POST['post_color']);
            }
            //echo "<pre>";print_r($post_data);
            $id = Wo_RegisterPost($post_data);
            //echo $id.'-----s';die;
            if ($id) 
            {
                Wo_CleanCache();
                Wo_UpdateUserDetails($wo['user'], true, false, false, true);
                if ($is_option == true) {
                    foreach ($_POST['answer'] as $key => $value) {
                        $add_opition = Wo_AddOption($id, $value);
                    }
                }
                if (isset($_FILES['postPhotos']['name'])) {
                    if (count($_FILES['postPhotos']['name']) > 0) {
                        for ($i = 0; $i < count($_FILES['postPhotos']['name']); $i++) {
                            $fileInfo = array(
                                'file' => $_FILES["postPhotos"]["tmp_name"][$i],
                                'name' => $_FILES['postPhotos']['name'][$i],
                                'size' => $_FILES["postPhotos"]["size"][$i],
                                'type' => $_FILES["postPhotos"]["type"][$i],
                                'types' => 'jpg,png,jpeg,gif'
                            );
                            $file     = Wo_ShareFile($fileInfo, 1);
                            $image_file = Wo_GetMedia($file['filename']);
                            if ($wo['config']['adult_images'] == 1  && !detect_safe_search($image_file) && $wo['config']['adult_images_action'] == 1) {
                                $blur = 1;
                            }
                            elseif ($wo['config']['adult_images'] == 1 && detect_safe_search($image_file) == false && $wo['config']['adult_images_action'] == 0) {
                                $invalid_file = 3;
                                $errors[] = $error_icon . $wo['lang']['adult_image_file'];
                                Wo_DeletePost($id);
                                @unlink($file['filename']);
                                Wo_DeleteFromToS3($file['filename']);
                            }
                            if (!empty($file)) {
                                $media_album = Wo_RegisterAlbumMedia($id, $file['filename']);
                                $post_data['multi_image'] = 0;
                                $post_data['multi_image_post'] = 1;
                                $post_data['album_name'] = '';
                                $post_data['postFile'] = $file['filename'];
                                $post_data['postFileName'] = $file['name'];
                                $post_data['active'] = $post_active;
                                $new_id = Wo_RegisterPost($post_data);
                                $media_album = Wo_RegisterAlbumMedia($new_id, $file['filename'],$id);
                            }
                        }
                    }
                }
                if ($wo['config']['post_approval'] == 1 && !Wo_IsAdmin()) {
                    $data = array(
                        'status' => 200,
                        'invalid_file' => 4,
                        'html' => '',
                    );
                }
                else{
                    $wo['story'] = Wo_PostData($id);
                    // if($_POST['post_type']=='group')
                    // {
                    //    $html .= Wo_LoadPage('story/content-hub'); 
                    // }
                    // else
                    // {
                    //     $html .= Wo_LoadPage('story/content');
                    // }
                    
                    $data = array(
                        'status' => 200,
                        //'html' => $html,
                        'invalid_file' => $invalid_file,
                        'post_count' => (!empty($wo['story']['publisher']['details']) ? $wo['story']['publisher']['details']['post_count'] : 0)
                    );
                } 
            } 
            else 
            {
                $data = array(
                    'status' => 400,
                    'invalid_file' => $invalid_file
                );
            }
        } 
        else 
        {
            header("Content-type: application/json");
            echo json_encode(array(
                'status' => 400,
                'errors' => $errors,
                'invalid_file' => $invalid_file
            ));
            exit();
        }
        header("Content-type: application/json");
        echo json_encode($data);
        exit();



}