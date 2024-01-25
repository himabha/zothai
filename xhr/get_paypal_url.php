<?php 
if ($f == 'get_paypal_url') {
    $data = array(
        'status' => 400,
        'url' => ''
    );
  // echo "<pre>";print_r($_POST);
    if (isset($_POST['type'])) {
        $type2 = '';
        if (!empty($_POST['type2'])) {
            $type2 = $_POST['type2'];
        }
        //$url = Wo_PayPal($_POST['type'], $type2);
        $url = Wo_PayPal_For_group_board($_POST['type'], $type2);
        //echo "<pre>";print_r($url);die;
        if (!empty($url['type'])) {
            if ($url['type'] == 'SUCCESS' && !empty($url['type'])) {
                $data = array(
                    'status' => 200,
                    'url' => $url['url']
                );
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
