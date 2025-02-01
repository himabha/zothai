<?php
if ($s == 'create_my_page') {
    if (empty($_POST['page_name']) || empty($_POST['page_title']) || empty(Wo_Secure($_POST['page_title'])) || Wo_CheckSession($hash_id) === false) {
        $errors[] = $error_icon . $wo['lang']['please_check_details'];
    } else {
        if (!empty($_POST['page_id'])) {
            $query = mysqli_query($sqlConnect, "SELECT COUNT(`page_id`) as pages FROM " . T_PAGES . " WHERE `page_title` LIKE '" . Wo_Secure($_POST['page_title']) . "' and page_id != {$_POST['page_id']} and user_id = {$wo['user']['user_id']}");
            $fetched_data = mysqli_fetch_assoc($query);
            if ($fetched_data['pages'] > 0) {
                $errors[] = $error_icon . $wo['lang']['page_name_exists'];
            }
        } else {
            $query = mysqli_query($sqlConnect, "SELECT COUNT(`page_id`) as pages FROM " . T_PAGES . " WHERE `page_title` LIKE '" . Wo_Secure($_POST['page_title']) . "' and user_id = {$wo['user']['user_id']}");
            $fetched_data = mysqli_fetch_assoc($query);
            if ($fetched_data['pages'] > 0) {
                $errors[] = $error_icon . $wo['lang']['page_name_exists'];
            }
        }

        /* if (in_array($_POST['page_name'], $wo['site_pages'])) {
            $errors[] = $error_icon . $wo['lang']['page_name_invalid_characters'];
        } */
        if (/* strlen($_POST['page_name']) < 5 or  */ strlen($_POST['page_name']) > 32) {
            $errors[] = $error_icon . "Page name length must contain maximum 32 characters";
        }
        /* if (!preg_match('/^[\w_]+$/', $_POST['page_name'])) {
            $errors[] = $error_icon . $wo['lang']['page_name_invalid_characters'];
        } */
        if (empty($_POST['page_category'])) {
            $_POST['page_category'] = 1;
        }
    }

    if (empty($errors)) {
        if ($_POST['page_id']) {
            $query = mysqli_query($sqlConnect, "UPDATE " . T_PAGES . " SET page_title = '" . Wo_Secure($_POST['page_title']) . "', page_name = '" . Wo_Secure($_POST['page_name']) . "' WHERE user_id = {$wo['user']['user_id']} and page_id = {$_POST['page_id']}");
            if ($query) {
                $data = array(
                    'status' => 200,
                    'location' => Wo_SeoLink($_POST['profile_page'] . "?page=" . Wo_Secure($_POST['page_id']))
                );
            } else {
                $errors[] = $error_icon . mysqli_error($sqlConnect);
            }
        } else {
            $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_PAGES . " WHERE user_id = {$wo['user']['user_id']}");
            if ($query && mysqli_num_rows($query) < 6) {
                $re_page_data = array(
                    'page_name' => Wo_Secure($_POST['page_name']),
                    'user_id' => Wo_Secure($wo['user']['user_id']),
                    'page_title' => Wo_Secure($_POST['page_title']),
                    'page_description' => Wo_Secure($_POST['page_description']),
                    'page_category' => Wo_Secure($_POST['page_category']),
                    'active' => '1'
                );
                if (isset($wo['user']['avatar'])) {
                    $re_page_data['avatar'] = $wo['user']['avatar_org'];
                }
                $register_page = Wo_RegisterPage($re_page_data);
                if ($register_page) {
                    $data = array(
                        'status' => 200,
                        'location' => Wo_SeoLink($_POST['profile_page'] . "?page=" . Wo_Secure($register_page))
                    );
                }
            } else {
                $errors[] = $error_icon . "Maximum limit to add new page is 6";
            }
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
if ($s === 'delete_my_page' && !empty($_POST['page_id'])) {
    $query = mysqli_query($sqlConnect, "DELETE FROM " . T_PAGES . " WHERE user_id = {$wo['user']['user_id']} and page_id = {$_POST['page_id']}");
    if ($query) {
        $data = array(
            'status' => 200,
            'location' => Wo_SeoLink($_POST['profile_page'])
        );
    } else {
        $errors[] = $error_icon . mysqli_error($sqlConnect);
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
