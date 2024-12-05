<?php
if ($f == 'create_my_page') {
    if (empty($_POST['page_name']) || empty($_POST['page_title']) || empty(Wo_Secure($_POST['page_title'])) || Wo_CheckSession($hash_id) === false) {
        $errors[] = $error_icon . $wo['lang']['please_check_details'];
    } else {
        $is_exist = Wo_IsNameExist($_POST['page_name'], 0);
        if (in_array(true, $is_exist)) {
            $errors[] = $error_icon . $wo['lang']['page_name_exists'];
        }
        if (in_array($_POST['page_name'], $wo['site_pages'])) {
            $errors[] = $error_icon . $wo['lang']['page_name_invalid_characters'];
        }
        if (strlen($_POST['page_name']) < 5 or strlen($_POST['page_name']) > 32) {
            $errors[] = $error_icon . $wo['lang']['page_name_characters_length'];
        }
        if (!preg_match('/^[\w_]+$/', $_POST['page_name'])) {
            $errors[] = $error_icon . $wo['lang']['page_name_invalid_characters'];
        }
        if (empty($_POST['page_category'])) {
            $_POST['page_category'] = 1;
        }
    }

    if (empty($errors)) {
        $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_PAGES . " WHERE user_id = {$wo['user']['user_id']}");
        if ($query && mysqli_num_rows($query) < 6) {
            $re_page_data  = array(
                'page_name' => Wo_Secure($_POST['page_name']),
                'user_id' => Wo_Secure($wo['user']['user_id']),
                'page_title' => Wo_Secure($_POST['page_title']),
                'page_description' => Wo_Secure($_POST['page_description']),
                'page_category' => Wo_Secure($_POST['page_category']),
                'active' => '1'
            );
            $register_page = Wo_RegisterPage($re_page_data);

            if ($register_page) {
                $data = array(
                    'status' => 200,
                    'location' => Wo_SeoLink($_POST['profile_page'] . "?page=" . Wo_Secure($_POST['page_name']))
                );
            }
        } else {
            $errors[] = $error_icon . "Maximum limit to add new page is 7";
        }
    }
    header("Content-type: application/json");
    if (isset($errors)) {
        echo json_encode(array(
            'errors' => $errors
        ));
    } else {
        echo json_encode($data);
    }
    exit();
}
