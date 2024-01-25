<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(0);

@ini_set('max_execution_time', 0);
require_once('config.php');

require_once('assets/libraries/DB/vendor/autoload.php');

// require 'assets/libraries/ffmpeg-class/vendor/autoload.php';
$wo           = array();
// Connect to SQL Server
$sqlConnect   = $wo['sqlConnect'] = mysqli_connect($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name, 3306);
// Handling Server Errors
$ServerErrors = array();
if (mysqli_connect_errno()) {
    $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (!function_exists('curl_init')) {
    $ServerErrors[] = "PHP CURL is NOT installed on your web server !";
}
if (!extension_loaded('gd') && !function_exists('gd_info')) {
    $ServerErrors[] = "PHP GD library is NOT installed on your web server !";
}
if (!extension_loaded('zip')) {
    $ServerErrors[] = "ZipArchive extension is NOT installed on your web server !";
}

$query = mysqli_query($sqlConnect, "SET NAMES utf8mb4");
if (isset($ServerErrors) && !empty($ServerErrors)) {
    foreach ($ServerErrors as $Error) {
        echo "<h3>" . $Error . "</h3>";
    }
    die();
}

$baned_ips = Wo_GetBanned('user');

if (in_array($_SERVER["REMOTE_ADDR"], $baned_ips)) {
    exit();
}

$config              = Wo_GetConfig();
$db                  = new MysqliDb($sqlConnect);

$all_langs           = Wo_LangsNamesFromDB();

foreach ($all_langs as $key => $value) {
    $insert = false;
    if (!in_array($value, array_keys($config))) {
        $db->insert(T_CONFIG,array('name' => $value, 'value' => 1));
        $insert = true;
    }
}
if ($insert == true) {
    $config = Wo_GetConfig();
}

if( ISSET( $_GET['theme'] ) && in_array($_GET['theme'], ['default', 'sunshine', 'wowonder'])){
    $_SESSION['theme'] = $_GET['theme'];
}

if( ISSET( $_SESSION['theme'] ) ){
    $config['theme'] = $_SESSION['theme'];
    if( $_SERVER["REQUEST_URI"] == "/v2/wonderful" || $_SERVER["REQUEST_URI"] == "/v2/wowonder" ){
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
}

// Config Url
$config['theme_url'] = $site_url . '/themes/' . $config['theme'];

$config['site_url']  = $site_url;
$wo['site_url']      = $site_url;
$s3_site_url         = 'https://test.s3.amazonaws.com';
if (!empty($config['bucket_name'])) {
    $s3_site_url = "https://{bucket}.s3.amazonaws.com";
    $s3_site_url = str_replace('{bucket}', $config['bucket_name'], $s3_site_url);
}
$config['s3_site_url'] = $s3_site_url;


$wo['config']              = $config;
$ccode                     = Wo_CustomCode('g');
$ccode                     = (is_array($ccode))  ? $ccode    : array();
$wo['config']['header_cc'] = (!empty($ccode[0])) ? $ccode[0] : '';
$wo['config']['footer_cc'] = (!empty($ccode[1])) ? $ccode[1] : '';
$wo['config']['styles_cc'] = (!empty($ccode[2])) ? $ccode[2] : '';

$wo['site_pages'] = array(
    'home',
    'welcome',
    'activate',
    'search',
    'timeline',
    'pages',
    'page',
    'groups',
    'group',
    'create-group',
    'group-setting',
    'create-page',
    'setting',
    'page-setting',
    'messages',
    'logout',
    '404',
    'post',
    'games',
    'saved-posts',
    'hashtag',
    'terms',
    'contact-us',
    'albums',
    'album',
    'game',
    'go_pro',
    'upgraded',
    'oops',
    'user_activation',
    'boosted-pages',
    'boosted-posts',
    'video-call',
    'read-blog',
    'blog',
    'My-Blogs',
    'edit-blog',
    'create_blog',
    'developers',
    'ads',
    'password-reset',
    'admin-cp',
    'admincp',
    'adminPages',
    'start-up',
    'activated',
    'ads-create',
    'app',
    'messages',
    'terms',
    'video-call',
    'video-call-api',
    'post',
    'game',
    'upgraded',
    'get_news_feed',
    'new-game',
    'go-pro',
    'oops',
    'user-activation',
    'hashtag',
    'follow-requests',
    '404',
    'register',
    'confirm-sms',
    'forgot-password',
    'activate',
    'pages',
    'create-group',
    'create-page',
    'logout',
    'contact-us',
    'setting',
    'messages',
    'albums',
    'album',
    'products',
    'my-products',
    'blogs',
    'sharer',
    'app_api',
    'api_request',
    'authorize',
    'advertise'
);

$wo['script_version']  = $wo['config']['version'];
$http_header           = 'http://';
if (!empty($_SERVER['HTTPS'])) {
    $http_header = 'https://';
}
$wo['actual_link']   = $http_header . $_SERVER['HTTP_HOST'] . urlencode($_SERVER['REQUEST_URI']);
// Define Cache Vireble
$cache               = new Cache();

if (!is_dir('cache')) {
    $cache->Wo_OpenCacheDir();
}

$wo['purchase_code'] = '';
if (!empty($purchase_code)) {
    $wo['purchase_code'] = $purchase_code;
}



// Login With Url
$wo['facebookLoginUrl']   = $config['site_url'] . '/login-with.php?provider=Facebook';
$wo['twitterLoginUrl']    = $config['site_url'] . '/login-with.php?provider=Twitter';
$wo['googleLoginUrl']     = $config['site_url'] . '/login-with.php?provider=Google';
$wo['linkedInLoginUrl']   = $config['site_url'] . '/login-with.php?provider=LinkedIn';
$wo['VkontakteLoginUrl']  = $config['site_url'] . '/login-with.php?provider=Vkontakte';
$wo['instagramLoginUrl']  = $config['site_url'] . '/login-with.php?provider=Instagram';
// Defualt User Pictures 
$wo['userDefaultAvatar']  = 'upload/photos/d-avatar.jpg';
$wo['userDefaultCover']   = 'upload/photos/d-cover.jpg';
$wo['pageDefaultAvatar']  = 'upload/photos/d-page.jpg';
$wo['groupDefaultAvatar'] = 'upload/photos/d-group.jpg';
// Get LoggedIn User Data
$wo['loggedin']           = false;
$langs                    = Wo_LangsNamesFromDB();
if (Wo_IsLogged() == true) {
    $session_id         = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
    $wo['user_session'] = Wo_GetUserFromSessionID($session_id);
    $wo['user']         = Wo_UserData($wo['user_session']);
    if (!empty($wo['user']['language'])) {
        if (in_array($wo['user']['language'], $langs)) {
            $_SESSION['lang'] = $wo['user']['language'];
        }
    }
    if ($wo['user']['user_id'] < 0 || empty($wo['user']['user_id']) || !is_numeric($wo['user']['user_id']) || Wo_UserActive($wo['user']['username']) === false) {
        header("Location: " . Wo_SeoLink('index.php?link1=logout'));
    }
    $wo['loggedin'] = true;
}

if (!empty($_GET['c_id']) && !empty($_GET['user_id'])) {
    $application = 'windows';
    if (!empty($_GET['application'])) {
        if ($_GET['application'] == 'phone') {
            $application = Wo_Secure($_GET['application']);
        }
    }
    $c_id             = Wo_Secure($_GET['c_id']);
    $user_id          = Wo_Secure($_GET['user_id']);
    $check_if_session = Wo_CheckUserSessionID($user_id, $c_id, $application);
    if ($check_if_session === true) {
        $wo['user']          = Wo_UserData($user_id);
        $session             = Wo_CreateLoginSession($user_id);
        $_SESSION['user_id'] = $session;
        setcookie("user_id", $session, time() + (10 * 365 * 24 * 60 * 60));
        if ($wo['user']['user_id'] < 0 || empty($wo['user']['user_id']) || !is_numeric($wo['user']['user_id']) || Wo_UserActive($wo['user']['username']) === false) {
            header("Location: " . Wo_SeoLink('index.php?link1=logout'));
        }
        $wo['loggedin'] = true;
    }
}
if (!empty($_POST['user_id']) && (!empty($_POST['s']) || !empty($_POST['access_token']))) {
    $application = 'windows';
    $access_token = (!empty($_POST['s'])) ? $_POST['s'] : $_POST['access_token'];
    if (!empty($_GET['application'])) {
        if ($_GET['application'] == 'phone') {
            $application = Wo_Secure($_GET['application']);
        }
    }
    if ($application == 'windows') {
        $access_token = $access_token;
    }
    $s                = Wo_Secure($access_token);
    $user_id          = Wo_Secure($_POST['user_id']);
    $check_if_session = Wo_CheckUserSessionID($user_id, $s, $application);
    if ($check_if_session === true) {
        $wo['user'] = Wo_UserData($user_id);
        if ($wo['user']['user_id'] < 0 || empty($wo['user']['user_id']) || !is_numeric($wo['user']['user_id']) || Wo_UserActive($wo['user']['username']) === false) {
            $json_error_data = array(
                'api_status' => '400',
                'api_text' => 'failed',
                'errors' => array(
                    'error_id' => '7',
                    'error_text' => 'User id is wrong.'
                )
            );
            header("Content-type: application/json");
            echo json_encode($json_error_data, JSON_PRETTY_PRINT);
            exit();
        }
        $wo['loggedin'] = true;
    } else {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'errors' => array(
                'error_id' => '6',
                'error_text' => 'Session id is wrong.'
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
}
// Language Function
if (isset($_GET['lang']) AND !empty($_GET['lang'])) {
    if (in_array($_GET['lang'], array_keys($wo['config'])) && $wo['config'][$_GET['lang']] == 1) {
        $lang_name = Wo_Secure(strtolower($_GET['lang']));
        if (in_array($lang_name, $langs)) {
            Wo_CleanCache();
            $_SESSION['lang'] = $lang_name;
            if ($wo['loggedin'] == true) {
                mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `language` = '" . $lang_name . "' WHERE `user_id` = " . Wo_Secure($wo['user']['user_id']));
            }
        }
    }
}
if ($wo['loggedin'] == true && $wo['config']['cache_sidebar'] == 1) {
    if (!empty($_COOKIE['last_sidebar_update'])) {
        if ($_COOKIE['last_sidebar_update'] < (time() - 120)) {
            Wo_CleanCache();
        }
    } else {
        Wo_CleanCache();
    }
}
if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = $wo['config']['defualtLang'];
}
$wo['language']      = $_SESSION['lang'];
$wo['language_type'] = 'ltr';
// Add rtl languages here.
$rtl_langs           = array(
    'arabic'
);
if (!isset($_COOKIE['ad-con'])) {
    setcookie('ad-con', htmlentities(json_encode(array(
        'date' => date('Y-m-d'),
        'ads' => array()
    ))), time() + (10 * 365 * 24 * 60 * 60));
}
$wo['ad-con'] = array();
if (!empty($_COOKIE['ad-con'])) {
    $wo['ad-con'] = json_decode(html_entity_decode($_COOKIE['ad-con']));
    $wo['ad-con'] = ToArray($wo['ad-con']);
}
if (!is_array($wo['ad-con']) || !isset($wo['ad-con']['date']) || !isset($wo['ad-con']['ads'])) {
    setcookie('ad-con', htmlentities(json_encode(array(
        'date' => date('Y-m-d'),
        'ads' => array()
    ))), time() + (10 * 365 * 24 * 60 * 60));
}
if (is_array($wo['ad-con']) && isset($wo['ad-con']['date']) && strtotime($wo['ad-con']['date']) < strtotime(date('Y-m-d'))) {
    setcookie('ad-con', htmlentities(json_encode(array(
        'date' => date('Y-m-d'),
        'ads' => array()
    ))), time() + (10 * 365 * 24 * 60 * 60));
}

if (!isset($_COOKIE['_us'])) {
    setcookie('_us', time() + (60 * 60 * 24) , time() + (10 * 365 * 24 * 60 * 60));
}

if (isset($_COOKIE['_us']) && $_COOKIE['_us'] < time() || 1) {
    setcookie('_us', time() + (60 * 60 * 24) , time() + (10 * 365 * 24 * 60 * 60));
    $expired_stories = $db->where('expire',time(),'<')->get(T_USER_STORY);
    foreach ($expired_stories as $key => $value) {
        $db->where('story_id',$value->id)->delete(T_STORY_SEEN);
    }
    @mysqli_query($sqlConnect, "DELETE FROM " . T_USER_STORY_MEDIA . " WHERE `expire` < ".time());
    @mysqli_query($sqlConnect, "DELETE FROM " . T_USER_STORY . " WHERE `expire` < ".time());
}


// checking if corrent language is rtl.
foreach ($rtl_langs as $lang) {
    if ($wo['language'] == strtolower($lang)) {
        $wo['language_type'] = 'rtl';
    }
}
// Icons Virables
$error_icon   = '<i class="fa fa-exclamation-circle"></i> ';
$success_icon = '<i class="fa fa-check"></i> ';
// Include Language File
$wo['lang']   = Wo_LangsFromDB($wo['language']);
if (file_exists('assets/languages/extra/' . $wo['language'] . '.php')) {
    require 'assets/languages/extra/' . $wo['language'] . '.php';
}
if (empty($wo['lang'])) {
    $wo['lang'] = Wo_LangsFromDB();
}

$wo['second_post_button_icon']  = ($config['second_post_button'] == 'wonder') ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-thumbs-down"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"></path></svg>';
$theme_settings = array();
$theme_settings['theme'] = 'wowonder';

if (file_exists('./themes/' . $config['theme'] . '/layout/404/dont-delete-this-file.json')) {
    $theme_settings = json_decode(file_get_contents('./themes/' . $config['theme'] . '/layout/404/dont-delete-this-file.json'), true);
}
if ($theme_settings['theme'] == 'wonderful') {
    $wo['second_post_button_icon']  = ($config['second_post_button'] == 'wonder') ? 'exclamation-circle' : 'thumb-down';
}

$wo['second_post_button_text']  = ($config['second_post_button'] == 'wonder') ? $wo['lang']['wonder'] : $wo['lang']['dislike'];
$wo['second_post_button_texts'] = ($config['second_post_button'] == 'wonder') ? $wo['lang']['wonders'] : $wo['lang']['dislikes'];


$wo['marker']                   = '?';
if ($wo['config']['seoLink'] == 0) {
    $wo['marker'] = '&';
}
$wo['feelingIcons']                  = array(
    'happy' => 'smile',
    'loved' => 'heart-eyes',
    'sad' => 'disappointed',
    'so_sad' => 'sob',
    'angry' => 'angry',
    'confused' => 'confused',
    'smirk' => 'smirk',
    'broke' => 'broken-heart',
    'expressionless' => 'expressionless',
    'cool' => 'sunglasses',
    'funny' => 'joy',
    'tired' => 'tired-face',
    'lovely' => 'heart',
    'blessed' => 'innocent',
    'shocked' => 'scream',
    'sleepy' => 'sleeping',
    'pretty' => 'relaxed',
    'bored' => 'unamused'
);

/*$emo = array(
    ':)' => 'smile',
    '(&lt;' => 'joy',
    '**)' => 'relaxed',
    ':p' => 'stuck-out-tongue-winking-eye',
    ':_p' => 'stuck-out-tongue',
    'B)' => 'sunglasses',
    ';)' => 'wink',
    ':D' => 'grin',
    '/_)' => 'smirk',
    '0)' => 'innocent',
    ':_(' => 'cry',
    ':__(' => 'sob',
    ':(' => 'disappointed',
    ':*' => 'kissing-heart',
    '&lt;3' => 'heart',
    '&lt;/3' => 'broken-heart',
    '*_*' => 'heart-eyes',
    '&lt;5' => 'star',
    ':o' => 'open-mouth',
    ':0' => 'scream',
    'o(' => 'anguished',
    '-_(' => 'unamused',
    'x(' => 'angry',
    'X(' => 'rage',
    '-_-' => 'expressionless',
    ':-/' => 'confused',
    ':|' => 'neutral-face',
    '!_' => 'exclamation',
    ':|' => 'neutral-face',
    ':|' => 'neutral-face',
    ':yum:' => 'yum',
    ':triumph:' => 'triumph',
    ':imp:' => 'imp',
    ':hear_no_evil:' => 'hear-no-evil',
    ':alien:' => 'alien',
    ':yellow_heart:' => 'yellow-heart',
    ':sleeping:' => 'sleeping',
    ':mask:' => 'mask',
    ':no_mouth:' => 'no-mouth',
    ':weary:' => 'weary',
    ':dizzy_face:' => 'dizzy-face',
    ':man:' => 'man',
    ':woman:' => 'woman',
    ':boy:' => 'boy',
    ':girl:' => 'girl',
    ':оlder_man:' => 'older-man',
    ':оlder_woman:' => 'older-woman',
    ':cop:' => 'cop',
    ':dancers:' => 'dancers',
    ':speak_no_evil:' => 'speak-no-evil',
    ':lips:' => 'lips',
    ':see_no_evil:' => 'see-no-evil',
    ':dog:' => 'dog',
    ':bear:' => 'bear',
    ':rose:' => 'rose',
    ':gift_heart:' => 'gift-heart',
    ':ghost:' => 'ghost',
    ':bell:' => 'bell',
    ':video_game:' => 'video-game',
    ':soccer:' => 'soccer',
    ':books:' => 'books',
    ':moneybag:' => 'moneybag',
    ':mortar_board:' => 'mortar-board',
    ':hand:' => 'hand',
    ':tiger:' => 'tiger',
    ':elephant:' => 'elephant',
    ':scream_cat:' => 'scream-cat',
    ':monkey:' => 'monkey',
    ':bird:' => 'bird',
    ':snowflake:' => 'snowflake',
    ':sunny:' => 'sunny',
    ':оcean:' => 'ocean',
    ':umbrella:' => 'umbrella',
    ':hibiscus:' => 'hibiscus',
    ':tulip:' => 'tulip',
    ':computer:' => 'computer',
    ':bomb:' => 'bomb',
    ':gem:' => 'gem',
    ':ring:' => 'ring'
);*/
$emo = array(
    ':)' => 'smile',
    '(&lt;' => 'joy',
    '**)' => 'relaxed',
    ':p' => 'stuck-out-tongue-winking-eye',
    ':_p' => 'stuck-out-tongue',
    'B)' => 'sunglasses',
    ';)' => 'wink',
    ':D' => 'grin',
    '/_)' => 'smirk',
    '0)' => 'innocent',
    ':_(' => 'cry',
    ':__(' => 'sob',
    ':(' => 'disappointed',
    ':*' => 'kissing-heart',
    '&lt;3' => 'heart',
    '&lt;/3' => 'broken-heart',
    '*_*' => 'heart-eyes',
    '&lt;5' => 'star',
    ':o' => 'open-mouth',
    ':0' => 'scream',
    'o(' => 'anguished',
    '-_(' => 'unamused',
    'x(' => 'angry',
    'X(' => 'rage',
    '-_-' => 'expressionless',
    ':-/' => 'confused',
    ':|' => 'neutral-face',
    '!_' => 'exclamation',
    ':|' => 'neutral-face',
    ':|' => 'neutral-face',
    ':blush:' => 'blush',
':grinning:' => 'grinning',
':yum:' => 'yum',
':kissing:' => 'kissing',
':kissing-smiling-eyes:' => 'kissing-smiling-eyes',
':kissing-closed-eyes:' => 'kissing-closed-eyes',
':no-mouth:' => 'no-mouth',
':Persevere:' => 'persevere',
':hushed:' => 'hushed',
':sleepy:' => 'sleepy',
':tired-face:' => 'tired-face',
':sleeping:' => 'sleeping',
':relieved:' => 'relieved',
':stuck-out-tongue-closed-eyes:' => 'stuck-out-tongue-closed-eyes',
':astonished:' => 'astonished',
':mask:' => 'mask',
':confounded:' => 'confounded',
':worried:' => 'worried',
':triumph:' => 'triumph',
':frowning:' => 'frowning',
':fearful:' => 'fearful',
':weary:' => 'weary',
':flushed:' => 'flushed',
':dizzy-face:' => 'dizzy-face',
':smiling-imp:' => 'smiling-imp',
':japanese-ogre:' => 'japanese-ogre',
':japanese-goblin:' => 'japanese-goblin',
':skull:' => 'skull',
':alien:' => 'alien',
':ghost:' => 'ghost',
':Poop:' => 'poop',
':smiley-cat:' => 'smiley-cat',
':smile-cat:' => 'smile-cat',
':joy-cat:' => 'joy-cat',
':heart-eyes-cat:' => 'heart-eyes-cat',
':smirk-cat:' => 'smirk-cat',
':kissing-cat:' => 'kissing-cat',
':scream-cat:' => 'scream-cat',
':crying-cat-face:' => 'crying-cat-face',
':Pouting-cat:' => 'pouting-cat',
':see-no-evil:' => 'see-no-evil',
':hear-no-evil:' => 'hear-no-evil',
':speak-no-evil:' => 'speak-no-evil',
':boy:' => 'boy',
':girl:' => 'girl',
':man:' => 'man',
':woman:' => 'woman',
':Older-man:' => 'older-man',
':Older-woman:' => 'older-woman',
':baby:' => 'baby',
':cop:' => 'cop',
':man-with-gua-pi-mao:' => 'man-with-gua-pi-mao',
':man-with-turban:' => 'man-with-turban',
':Princess:' => 'princess',
':bride-with-veil:' => 'bride-with-veil',
':guardsman:' => 'guardsman',
':santa:' => 'santa',
':angel:' => 'angel',
':massage:' => 'massage',
':haircut:' => 'haircut',
':Person-frowning:' => 'person-frowning',
':Person-with-pouting-face:' => 'person-with-pouting-face',
':Ok-woman:' => 'ok-woman',
':information-desk-person:' => 'information-desk-person',
':bow:' => 'bow',
':raised-hands:' => 'raised-hands',
':Pray:' => 'pray',
':bust-in-silhouette:' => 'bust-in-silhouette',
':walking:' => 'walking',
':runner:' => 'runner',
':dancers:' => 'dancers',
':dancer:' => 'dancer',
':gem:' => 'gem',
':ring:' => 'ring',
':couple:' => 'couple',
':family:' => 'family',
':two-men-holding-hands:' => 'two-men-holding-hands',
':two-women-holding-hands:' => 'two-women-holding-hands',
':couplekiss:' => 'couplekiss',
':v:' => 'v',
':hand:' => 'hand',
':Ok-hand:' => 'ok-hand',
':thumbsup:' => 'thumbsup',
':thumbsdown:' => 'thumbsdown',
':fist:' => 'fist',
':Punch:' => 'punch',
':wave:' => 'wave',
':Open-hands:' => 'open-hands',
':clap:' => 'clap',
':Point-right:' => 'point-right',
':Point-left:' => 'point-left',
':Point-up:' => 'point-up',
':Point-down:' => 'point-down',
':muscle:' => 'muscle',
':bride-with-veil:' => 'bride-with-veil',
':cupid:' => 'cupid',
':dizzy:' => 'dizzy',
':boom:' => 'boom',
':anger:' => 'anger',
':zzz:' => 'zzz',
':question:' => 'question',
':dash:' => 'dash',
':musical-note:' => 'musical-note',
':fire:' => 'fire',
':lips:' => 'lips',
':kiss:' => 'kiss',
':tongue:' => 'tongue',
':eyes:' => 'eyes',
':ear:' => 'ear',
':nose:' => 'nose',
':sunny:' => 'sunny',
':umbrella:' => 'umbrella',
':cloud:' => 'cloud',
':snowflake:' => 'snowflake',
':zap:' => 'zap',
':cyclone:' => 'cyclone',
':foggy:' => 'foggy',
':Ocean:' => 'ocean',
':sun-with-face:' => 'sun-with-face',
':full-moon-with-face:' => 'full-moon-with-face',
':blossom:' => 'blossom',
':bomb:' => 'bomb',
':earth-asia:' => 'earth-asia',
':swimmer:' => 'swimmer',
':bicyclist:' => 'bicyclist',
':e-mail:' => 'e-mail',
':microphone:' => 'microphone',
':headphones:' => 'headphones',
':radio:' => 'radio',
':saxophone:' => 'saxophone',
':guitar:' => 'guitar',
':house:' => 'house',
':house_with_garden:' => 'house-with-garden',
':school:' => 'school',
':Office:' => 'office',
':Post_office:' => 'post-office',
':hospital:' => 'hospital',
':bank:' => 'bank',
':convenience_store:' => 'convenience-store',
':love_hotel:' => 'love-hotel',
':hotel:' => 'hotel',
':wedding:' => 'wedding',
':church:' => 'church',
':department_store:' => 'department-store',
':european_post_office:' => 'european-post-office',
':city_sunrise:' => 'city-sunrise',
':city_sunset:' => 'city-sunset',
':japanese_castle:' => 'japanese-castle',
':european_castle:' => 'european-castle',
':tent:' => 'tent',
':factory:' => 'factory',
':tokyo_tower:' => 'tokyo-tower',
':japan:' => 'japan',
':mount_fuji:' => 'mount-fuji',
':sunrise_over_mountains:' => 'sunrise-over-mountains',
':sunrise:' => 'sunrise',
':stars:' => 'stars',
':statue_of_liberty:' => 'statue-of-liberty',
':bridge_at_night:' => 'bridge-at-night',
':carousel_horse:' => 'carousel-horse',
':rainbow:' => 'rainbow',
':ferris_wheel:' => 'ferris-wheel',
':fountain:' => 'fountain',
':roller_coaster:' => 'roller-coaster',
':ship:' => 'ship',
':speedboat:' => 'speedboat',
':boat:' => 'boat',
':sailboat:' => 'sailboat',
':rowboat:' => 'rowboat',
':anchor:' => 'anchor',
':rocket:' => 'rocket',
':airplane:' => 'airplane',
':helicopter:' => 'helicopter',
':steam_locomotive:' => 'steam-locomotive',
':tram:' => 'tram',
':mountain_railway:' => 'mountain-railway',
':bike:' => 'bike',
':aerial_tramway:' => 'aerial-tramway',
':suspension_railway:' => 'suspension-railway',
':mountain_cableway:' => 'mountain-cableway',
':tractor:' => 'tractor',
':blue_car:' => 'blue-car',
':Oncoming_automobile:' => 'oncoming-automobile',
':car:' => 'car',
':taxi:' => 'taxi',
':Oncoming_taxi:' => 'oncoming-taxi',
':articulated_lorry:' => 'articulated-lorry',
':bus:' => 'abus',
':Oncoming_bus:' => 'oncoming-bus',
':Police_car:' => 'police-car',
':Oncoming_police_car:' => 'oncoming-police-car',
':fire_engine:' => 'fire-engine',
':ambulance:' => 'ambulance',
/*':minibus:' => 'minibus',
':truck:' => 'truck',
':train:' => 'train',
':station:' => 'station',
':train2:' => 'train2',
':bullettrain_front:' => 'bullettrain-front',
':bullettrain_side:' => 'bullettrain-side',
':light_rail:' => 'light-rail',
':monorail:' => 'monorail',
':railway_car:' => 'railway-car',
':trolleybus:' => 'trolleybus',
':ticket:' => 'ticket',
':fuelpump:' => 'fuelpump',
':vertical_traffic_light:' => 'vertical-traffic-light',
':traffic_light:' => 'traffic-light',
':warning:' => 'warning',
':construction:' => 'construction',
':beginner:' => 'beginner',
':atm:' => 'atm',
':slot_machine:' => 'slot-machine',
':busstop:' => 'busstop',
':barber:' => 'barber',
':hotsprings:' => 'hotsprings',
':checkered-flag:' => 'checkered-flag',
':crossed_flags:' => 'crossed-flags',
':moyai:' => 'moyai',
':circus_tent:' => 'circus-tent',
':Performing_arts:' => 'performing-arts',
':round_pushpin:' => 'round-pushpin',
':triangular_flag_on_post:' => 'triangular-flag-on-post',
':house:' => 'house',
':school:' => 'school',
':Office:' => 'office',
':Post-office:' => 'post-office',
':hospital:' => 'hospital',
':bank:' => 'bank',
':hotel:' => 'hotel',
':wedding:' => 'wedding',
':sunrise:' => 'sunrise',
':stars:' => 'stars',
':ship:' => 'ship',
':boat:' => 'boat',
':airplane:' => 'airplane',
':helicopter:' => 'helicopter',
':car:' => 'car',
':taxi:' => 'taxi',
':bus:' => 'bus',
':train2:' => 'train2',
':station:' => 'station',
':Police-car:' => 'police-car',
':ambulance:' => 'ambulance',
':1:' => 'one',
':two:' => 'two',
':three:' => 'three',
':four:' => 'four',
':five:' => 'five',
':six:' => 'six',
':seven:' => 'seven',
':eight:' => 'eight',
':nine:' => 'nine',
':keycap_ten:' => 'keycap-ten',
':1234:' => '1234',
':zero:' => 'zero',
':hash:' => 'hash',
':symbols:' => 'symbols',
':abcd:' => 'abcd',
':abc:' => 'abc',
':arrow_lower_left:' => 'arrow-lower-left',
':arrow_lower_right:' => 'arrow-lower-right',
':arrow_up:' => 'arrow-up',
':arrow_upper_left:' => 'arrow-upper-left',
':arrow_upper_right:' => 'arrow-upper-right',
':arrow_double_down:' => 'arrow-double-down',
':arrow_double_up:' => 'arrow-double-up',
':arrow_down_small:' => 'arrow-down-small',
':arrow_heading_down:' => 'arrow-heading-down',
':arrow_heading_up:' => 'arrow-heading-up',
':leftwards_arrow_with_hook:' => 'leftwards-arrow-with-hook',
':arrow_right_hook:' => 'arrow-right-hook',
':left_right_arrow:' => 'left-right-arrow',
':arrow_up_small:' => 'arrow-up-small',
':arrows_clockwise:' => 'arrows-clockwise',
':arrows_counterclockwise:' => 'arrows-counterclockwise',
':rewind:' => 'rewind',
':fast_forward:' => 'fast-forward',
':information_source:' => 'information-source',
':.ok:' => 'ok',
':twisted_rightwards_arrows:' => 'twisted-rightwards-arrows',
':repeat:' => 'repeat',*/
':new:' => 'new',
':top:' => 'top',
':up:' => 'up',
':cool:' => 'cool',
':free:' => 'free',
':ng:' => 'ng',
':cinema:' => 'cinema',
':koko:' => 'koko',
':signal_strength:' => 'signal-strength',
':sunny:' => 'sunny',
':umbrella:' => 'umbrella',
':cloud:' => 'cloud',
':snowflake:' => 'snowflake',
':snowman:' => 'snowman',
':zap:' => 'zap',
':cyclone:' => 'cyclone',
':foggy:' => 'foggy',
':ocean:' => 'ocean',
':cat:' => 'cat',
':dog:' => 'dog',
':mouse:' => 'mouse',
':hamster:' => 'hamster',
':rabbit:' => 'rabbit',
':wolf:' => 'wolf',
':frog:' => 'frog',
':tiger:' => 'tiger',
':koala:' => 'koala',
':bear:' => 'bear',
':Pig:' => 'pig',
':Pig_nose:' => 'pig-nose',
':cow:' => 'cow',
':boar:' => 'boar',
':monkey:' => 'monkey',
':monkey_face:' => 'monkey-face',
':horse:' => 'horse',
':racehorse:' => 'racehorse',
':camel:' => 'camel',
':sheep:' => 'sheep',
':elephant:' => 'elephant',
':Panda_face:' => 'panda-face',
':snake:' => 'snake',
':bird:' => 'bird',
':baby_chick:' => 'baby-chick',
':hatched_chick:' => 'hatched-chick',
':hatching_chick:' => 'hatching-chick',
':chicken:' => 'chicken',
':Penguin:' => 'penguin',
':turtle:' => 'turtle',
':bug:' => 'bug',
':honeybee:' => 'honeybee',
':ant:' => 'ant',
':beetle:' => 'beetle',
':snail:' => 'snail',
':Octopus:' => 'octopus',
':tropical_fish:' => 'tropical-fish',
':fish:' => 'fish',
':whale:' => 'whale',
':whale2:' => 'whale2',
':cow2:' => 'cow2',
':ram:' => 'ram',
':rat:' => 'rat',
':water_buffalo:' => 'water-buffalo',
':tiger2:' => 'tiger2',
':rabbit2:' => 'rabbit2',
':dragon:' => 'dragon',
':goat:' => 'goat',
':rooster:' => 'rooster',
':dog2:' => 'dog2',
':Pig2:' => 'pig2',
':mouse2:' => 'mouse2',
':Ox:' => 'ox',
':dragon_face:' => 'dragon-face',
':blowfish:' => 'blowfish',
':crocodile:' => 'crocodile',
':dromedary_camel:' => 'dromedary-camel',
':leopard:' => 'leopard',
':cat2:' => 'cat2',
':Poodle:' => 'poodle',
':Paw_prints:' => 'paw-prints',
':bouquet:' => 'bouquet',
':cherry_blossom:' => 'cherry-blossom',
':tulip:' => 'tulip',
':four_leaf_clover:' => 'four-leaf-clover',
':rose:' => 'rose',
':sunflower:' => 'sunflower',
':hibiscus:' => 'hibiscus',
':maple_leaf:' => 'maple-leaf',
':leaves:' => 'leaves',
':fallen_leaf:' => 'fallen-leaf',
':herb:' => 'herb',
':mushroom:' => 'mushroom',
':cactus:' => 'cactus',
':Palm_tree:' => 'palm-tree',
':evergreen_tree:' => 'evergreen-tree',
':deciduous_tree:' => 'deciduous-tree',
':chestnut:' => 'chestnut',
':seedling:' => 'seedling',
':blossom:' => 'blossom',
':ear_of_rice:' => 'ear-of-rice',
':shell:' => 'shell',
':globe_with_meridians:' => 'globe-with-meridians',
':sun_with_face:' => 'sun-with-face',
':full_moon_with_face:' => 'full-moon-with-face',
':new_moon_with_face:' => 'new-moon-with-face',
':new_moon:' => 'new-moon',
':waxing_crescent_moon:' => 'waxing-crescent-moon',
':first_quarter_moon:' => 'first-quarter-moon',
':waxing_gibbous_moon:' => 'waxing-gibbous-moon',
':full_moon:' => 'full-moon',
':waning_gibbous_moon:' => 'waning-gibbous-moon',
':last_quarter_moon:' => 'last-quarter-moon',
':waning_crescent_moon:' => 'waning-crescent-moon',
':last_quarter_moon_with_face:' => 'last-quarter-moon-with-face',
':first_quarter_moon_with_face:' => 'first-quarter-moon-with-face',
':crescent_moon:' => 'crescent-moon',
':earth_africa:' => 'earth-africa',
':earth_americas:' => 'earth-americas',
':earth_asia:' => 'earth-asia',
':volcano:' => 'volcano',
':milky_way:' => 'milky-way',
':Partly_sunny:' => 'partly-sunny',
':bamboo:' => 'bamboo',
':gift_heart:' => 'gift-heart',
':dolls:' => 'dolls',
':school_satchel:' => 'school-satchel',
':mortar_board:' => 'mortar-board',
':flags:' => 'flags',
':fireworks:' => 'fireworks',
':sparkler:' => 'sparkler',
':wind_chime:' => 'wind-chime',
':rice_scene:' => 'rice-scene',
':jack_o_lantern:' => 'jack-o-lantern',
':santa:' => 'santa',
':christmas_tree:' => 'christmas-tree',
':gift:' => 'gift',
':bell:' => 'bell',
':no_bell:' => 'no-bell',
':tanabata_tree:' => 'tanabata-tree',
':tada:' => 'tada',
':confetti_ball:' => 'confetti-ball',
':balloon:' => 'balloon',
':crystal_ball:' => 'crystal-ball',
':cd:' => 'cd',
':dvd:' => 'dvd',
':floppy_disk:' => 'floppy-disk',
':camera:' => 'camera',
':video_camera:' => 'video-camera',
':movie_camera:' => 'movie-camera',
':computer:' => 'computer',
':tv:' => 'tv',
':iphone:' => 'iphone',
':Phone:' => 'phone',
':pager:' => 'pager',
':telephone_receiver:' => 'telephone-receiver',
':fax:' => 'fax',
':minidisc:' => 'minidisc',
':vhs:' => 'vhs',
':sound:' => 'sound',
':speaker:' => 'speaker',
':mute:' => 'mute',
':loudspeaker:' => 'loudspeaker',
':mega:' => 'mega',
':hourglass:' => 'hourglass',
':hourglass_flowing_sand:' => 'hourglass-flowing-sand',
':alarm_clock:' => 'alarm-clock',
':watch:' => 'watch',
':radio:' => 'radio',
':satellite:' => 'satellite',
':loop:' => 'loop',
':mag:' => 'mag',
':mag_right:' => 'mag-right',
':unlock:' => 'unlock',
':lock:' => 'lock',
':lock_with_ink_pen:' => 'lock-with-ink-pen',
':closed_lock_with_key:' => 'closed-lock-with-key',
':key:' => 'key',
':bulb:' => 'bulb',
':flashlight:' => 'flashlight',
':battery:' => 'battery',
':electric_plug:' => 'electric-plug',
':calling:' => 'calling',
':email:' => 'email',
':mailbox:' => 'mailbox',
':Postbox:' => 'postbox',
':bath:' => 'bath',
':bathtub:' => 'bathtub',
':shower:' => 'shower',
':toilet:' => 'toilet',
':wrench:' => 'wrench',
':hammer:' => 'hammer',
':nut_and_bolt:' => 'nut-and-bolt',
':seat:' => 'seat',
':moneybag:' => 'moneybag',
':yen:' => 'yen',
':dollar:' => 'dollar',
':Pound:' => 'pound',
':euro:' => 'euro',
':credit_card:' => 'credit-card',
':money_with_wings:' => 'money-with-wings',
':Outbox_tray:' => 'outbox-tray',
':Inbox_tray:' => 'inbox-tray',
':envelope:' => 'envelope',
':Incoming_envelope:' => 'incoming-envelope',
':Postal_horn:' => 'postal-horn',
':mailbox_closed:' => 'mailbox-closed',
':mailbox_with_no_mail:' => 'mailbox-with-no-mail',
':Package:' => 'package',
':door:' => 'door',
':smoking:' => 'smoking',
':bomb:' => 'bomb',
':gun:' => 'gun',
':hocho:' => 'hocho',
':Pill:' => 'pill',
':syringe:' => 'syringe',
':Page_facing_up:' => 'page-facing-up',
':Page_with_curl:' => 'page-with-curl',
':bookmark_tabs:' => 'bookmark-tabs',
':bar_chart:' => 'bar-chart',
':chart_with_upwards_trend:' => 'chart-with-upwards-trend',
':chart_with_downwards_trend:' => 'chart-with-downwards-trend',
':scroll:' => 'scroll',
':clipboard:' => 'clipboard',
':calendar:' => 'calendar',
':date:' => 'date',
':card_index:' => 'card-index',
':file_folder:' => 'file-folder',
':Open_file_folder:' => 'open-file-folder',
':scissors:' => 'scissors',
':Pushpin:' => 'pushpin',
':Paperclip:' => 'paperclip',
':black_nib:' => 'black-nib',
':Pencil2:' => 'pencil2',
':straight_ruler:' => 'straight-ruler',
':triangular_ruler:' => 'triangular-ruler',
':closed_book:' => 'closed-book',
':green_book:' => 'green-book',
':blue_book:' => 'blue-book',
':Orange_book:' => 'orange-book',
':notebook:' => 'notebook',
':notebook_with_decorative_cover:' => 'notebook-with-decorative-cover',
':ledger:' => 'ledger',
':books:' => 'books',
':bookmark:' => 'bookmark',
':microscope:' => 'microscope',
':telescope:' => 'telescope',
':newspaper:' => 'newspaper',
':football:' => 'football',
':basketball:' => 'basketball',
':soccer:' => 'soccer',
':baseball:' => 'baseball',
':tennis:' => 'tennis',
':8ball:' => '8ball',
':rugby_football:' => 'rugby-football',
':bowling:' => 'bowling',
':golf:' => 'golf',
':mountain_bicyclist:' => 'mountain-bicyclist',
':bicyclist:' => 'bicyclist',
':horse_racing:' => 'horse-racing',
':snowboarder:' => 'snowboarder',
':swimmer:' => 'swimmer',
':surfer:' => 'surfer',
':ski:' => 'ski',
':spades:' => 'spades',
':hearts:' => 'hearts',
':clubs:' => 'clubs',
':diamonds:' => 'diamonds',
':gem:' => 'gem',
':ring:' => 'ring',
':trophy:' => 'trophy',
':musical_score:' => 'musical-score',
':musical_keyboard:' => 'musical-keyboard',
':violin:' => 'violin',
':space_invader:' => 'space-invader',
':video_game:' => 'video-game',
':black_joker:' => 'black-joker',
':flower_playing_cards:' => 'flower-playing-cards',
':game_die:' => 'game-die',
':dart:' => 'dart',
':mahjong:' => 'mahjong',
':clapper:' => 'clapper',
':memo:' => 'memo',
':art:' => 'art',
':microphone:' => 'microphone',
':headphones:' => 'headphones',
':trumpet:' => 'trumpet',
':sandal:' => 'sandal',
':high_heel:' => 'high-heel',
':lipstick:' => 'lipstick',
':boot:' => 'boot',
':shirt:' => 'shirt',
':necktie:' => 'necktie',
':womans_clothes:' => 'womans-clothes',
':dress:' => 'dress',
':running_shirt_with_sash:' => 'running-shirt-with-sash',
':jeans:' => 'jeans',
':kimono:' => 'kimono',
':bikini:' => 'bikini',
':ribbon:' => 'ribbon',
':tophat:' => 'tophat',
':crown:' => 'crown',
/*':womans_hat:' => 'womans-hat',
':mans_shoe:' => 'mans-shoe',
':closed_umbrella:' => 'closed-umbrella',
':briefcase:' => 'briefcase',
':handbag:' => 'handbag',
':Pouch:' => 'pouch',
':Purse:' => 'purse',
':eyeglasses:' => 'eyeglasses',
':fishing_pole_and_fish:' => 'fishing-pole-and-fish',
':coffee:' => 'coffee',
':tea:' => 'tea',
':sake:' => 'sake',
':baby_bottle:' => 'baby-bottle',
':beer:' => 'beer',
':beers:' => 'beers',
':cocktail:' => 'cocktail',
':tropical_drink:' => 'tropical-drink',
':wine_glass:' => 'wine-glass',
':fork_and_knife:' => 'fork-and-knife',
':Pizza:' => 'pizza',
':hamburger:' => 'hamburger',
':fries:' => 'fries',
':Poultry_leg:' => 'poultry-leg',
':meat_on_bone:' => 'meat-on-bone',
':spaghetti:' => 'spaghetti',
':curry:' => 'curry',
':fried_shrimp:' => 'fried-shrimp',
':bento:' => 'bento',
':sushi:' => 'sushi',
':fish_cake:' => 'fish-cake',
':rice_ball:' => 'rice-ball',
':rice_cracker:' => 'rice-cracker',
':rice:' => 'rice',
':ramen:' => 'ramen',
':stew:' => 'stew',
':Oden:' => 'oden',
':dango:' => 'dango',
':egg:' => 'egg',
':bread:' => 'bread',
':doughnut:' => 'doughnut',
':custard:' => 'custard',
':icecream:' => 'icecream',
':ice_cream:' => 'ice-cream',
':shaved_ice:' => 'shaved-ice',
':birthday:' => 'birthday',
':cake:' => 'cake',
':cookie:' => 'cookie',
':chocolate_bar:' => 'chocolate-bar',
':candy:' => 'candy',
':lollipop:' => 'lollipop',
':honey_pot:' => 'honey-pot',
':apple:' => 'apple',
':green_apple:' => 'green-apple',
':tangerine:' => 'tangerine',
':lemon:' => 'lemon',
':cherries:' => 'cherries',
':grapes:' => 'grapes',
':watermelon:' => 'watermelon',
':strawberry:' => 'strawberry',
':Peach:' => 'peach',
':melon:' => 'melon',
':banana:' => 'banana',
':Pear:' => 'pear',
':Pineapple:' => 'pineapple',
':sweet_potato:' => 'sweet-potato',
':eggplant:' => 'eggplant',
':tomato:' => 'tomato',
':corn:' => 'corn',
':u5272:' => 'u5272',
':u5408:' => 'u5408',
':u55b6:' => 'u55b6',
':u6307:' => 'u6307',
':u6708:' => 'u6708',
':u6709:' => 'u6709',
':u6e80:' => 'u6e80',
':u7121:' => 'u7121',
':u7533:' => 'u7533',
':u7a7a:' => 'u7a7a',
':u7981:' => 'u7981',
':sa:' => 'sa',
':restroom:' => 'restroom',
':mens:' => 'mens',
':womens:' => 'womens',
':baby_symbol:' => 'baby-symbol',
':no_smoking:' => 'no-smoking',
':Parking:' => 'parking',
':wheelchair:' => 'wheelchair',
':metro:' => 'metro',
':baggage_claim:' => 'baggage-claim',
':accept:' => 'accept',
':wc:' => 'wc',
':Potable_water:' => 'potable-water',
':Put_litter_in_its_place:' => 'put-litter-in-its-place',
':secret:' => 'secret',
':congratulations:' => 'congratulations',
':m:' => 'm',
':Passport_control:' => 'passport-control',
':left_luggage:' => 'left-luggage',
':customs:' => 'customs',
':ideograph_advantage:' => 'ideograph-advantage',
':customs:' => 'customs',
':cl:' => 'cl',
':sos:' => 'sos',
':id:' => 'id',
':no_entry_sign:' => 'no-entry-sign',
':underage:' => 'underage',
':no_mobile_phones:' => 'no-mobile-phones',
':do_not_litter:' => 'do-not-litter',
':non-potable_water:' => 'non-potable-water',
':no_bicycles:' => 'no-bicycles',
':no_pedestrians:' => 'no-pedestrians',
':children_crossing:' => 'children-crossing',
':no_entry:' => 'no-entry',
':eight_spoked_asterisk:' => 'eight-spoked-asterisk',
':heart_decoration:' => 'heart-decoration',
':vs:' => 'vs',
':vibration_mode:' => 'vibration-mode',
':mobile_phone_off:' => 'mobile-phone-off',
':chart:' => 'chart',
':currency_exchange:' => 'currency-exchange',
':aries:' => 'aries',
':taurus:' => 'taurus',
':gemini:' => 'gemini',
':cancer:' => 'cancer',
':leo:' => 'leo',
':virgo:' => 'virgo',
':libra:' => 'libra',
':scorpius:' => 'scorpius',
':sagittarius:' => 'sagittarius',
':capricorn:' => 'capricorn',
':aquarius:' => 'aquarius',
':Pisces:' => 'pisces',
':Ophiuchus:' => 'ophiuchus',
':six_pointed_star:' => 'six-pointed-star',
':negative_squared_cross_mark:' => 'negative-squared-cross-mark',
':a:' => 'a',
':b:' => 'b',
':ab:' => 'ab',
':O2:' => 'o2', 
':diamond_shape_with_a_dot_inside:' => 'diamond-shape-with-a-dot-inside',
':recycle:' => 'recycle',
':end:' => 'end',
':On:' => 'on',
':soon:' => 'soon',
':heavy_dollar_sign:' => 'heavy-dollar-sign',
':copyright:' => 'copyright',
':registered:' => 'registered',
':tm:' => 'tm',
':x:' => 'x',
':bangbang:' => 'bangbang',
':interrobang:' => 'interrobang',
':O:' => 'o',
':heavy_multiplication_x:' => 'heavy-multiplication-x',
':heavy_minus_sign:' => 'heavy-minus-sign',
':heavy_division_sign:' => 'heavy-division-sign',
':white_flower:' => 'white-flower',
':100:' => '100',
':heavy_check_mark:' => 'heavy-check-mark',
':ballot_box_with_check:' => 'ballot-box-with-check',
':radio_button:' => 'radio-button',
':link:' => 'link',
':curly_loop:' => 'curly-loop',
':wavy_dash:' => 'wavy-dash',
':Part_alternation_mark:' => 'part-alternation-mark',
':trident:' => 'trident',
':black_small_square:' => 'black-small-square',
':white_small_square:' => 'white-small-square',
':black_medium_small_square:' => 'black-medium-small-square',
':white_medium_small_square:' => 'white-medium-small-square',
':black_medium_square:' => 'black-medium-square',
':white_medium_square:' => 'white-medium-square',
':black_large_square:' => 'black-large-square',
':white_large_square:' => 'white-large-square',
':white_check_mark:' => 'white-check-mark',
':black_square_button:' => 'black-square-button',
':white_square_button:' => 'white-square-button',
':black_circle:' => 'black-circle',
':white_circle:' => 'white-circle',
':red_circle:' => 'red-circle',
':large_blue_circle:' => 'large-blue-circle',
':large_blue_diamond:' => 'large-blue-diamond',
':large_orange_diamond:' => 'large-orange-diamond',
':small_blue_diamond:' => 'small-blue-diamond',
':small_orange_diamond:' => 'small-orange-diamond',
':small_red_triangle:' => 'small-red-triangle',
':small_red_triangle_down:' => 'small-red-triangle-down',
':afghanistan:'=> 'afghanistan-flag',
':antigua-barbuda:' => 'antigua-barbuda-flag',
':anguilla:' => 'anguilla-flag',
':albania:' => 'albania-flag',
':armenia:' => 'armenia-flag',
':angola:' => 'angola-flag',
':antarctica:' => 'antarctica-flag',
':argentina:' => 'argentina-flag',
':american-samoa:' => 'american-samoa-flag',
':austria:' => 'austria-flag',
':australia:' => 'australia-flag',
':aruba:' => 'aruba-flag',
':aland:' => 'aland-flag',
':azerbaijan:' => 'azerbaijan-flag',
':bosnia:' => 'bosnia-herzegovina-flag',
':barbados:' => 'barbados-flag',
':bangladesh:' => 'bangladesh-flag',
':belgium:' => 'belgium-flag',
':burkina:' => 'burkina-faso-flag',
':bulgaria:' => 'bulgaria-flag ',
':bahrain:' => 'bahrain-flag ',
':burundi:' => 'burundi-flag',
':benin:' => 'benin-flag ',
':barthelemy:' => 'barthelemy-barthelemy-saint-flag',
':bermuda:' => 'bermuda-flag',
':brunei:' => 'brunei-darussalam-flag',
':bolivia:' => 'bolivia-flag',
':bonaire:' => 'bonaire-caribbean-eustatius-netherlands-saba-sint-flag',
':brazil:' => 'brazil-flag',
':bahamas:' => 'bahamas-flag',
':bhutan:' => 'bhutan-flag',
':bouvet:' => 'bouvet-island-flag',
':botswana:' => 'botswana-flag',
':belarus:' => 'belarus-flag',
':belize:' => 'belize-flag',
':canada:' => 'canada-flag',
':cocos:' => 'cocos-island-keeling-flag',
':congo:' => 'congo-congo-kinshasa-democratic-republic-of-congo-drc-kinshasa-republic-flag',
':central:' => 'central-african-republic-republic-flag',
':brazzaville:' => 'brazzaville-congo-congo-republic-congo-brazzaville-republic-republic-of-the-congo-flag',
':switzerland:' => 'switzerland-flag',
':cote:' => 'cote-ivoire-cote-ivoire-ivory-coast-flag',
':chile:' => 'chile-flag',
':cameroon:' => 'cameroon-flag',
':colombia:' => 'colombia-flag',
':clipperton:' => 'clipperton-island-flag',
':costa-rica:' => 'costa-rica-flag',
':cuba:' => 'cuba-flag',
':cabo:' => 'cabo-cape-verde-flag',
':antilles:' => 'antilles-curacao-curaÃ§ao-flag',
':christmas:' => 'christmas-island-flag',
':cyprus:' => 'cyprus-flag',
':czech:' => 'czech-republic-flag',
':germany:' => 'germany-flag',
':diego:' => 'diego-garcia-flag',
':djibouti:' => 'djibouti-flag',
':denmark:' => 'denmark-flag',
':dominica:' => 'dominica-flag',
':dominican:' => 'dominican-republic-flag',
':algeria:' => 'algeria-flag',
':ceuta:' => 'ceuta-melilla-flag',
':ecuador:' => 'ecuador-flag',
':estonia:' => 'estonia-flag',
':egypt:' => 'egypt-flag',
':sahara:' => 'sahara-west-western-sahara-flag',
':eritrea:' => 'eritrea-flag',
':spain:' => 'spain-flag',
':ethiopia:' => 'ethiopia-flag',
':european:' => 'european-union-flag',
':finland:' => 'finland-flag',
':fiji:' => 'fiji-flag',
':falkland:' => 'falkland-falklands-island-islas-malvinas-flag',
':micronesia:' => 'micronesia-flag',
':faroe:' => 'faroe-island-flag',
':france:' => 'france-flag',
':gabon:' => 'gabon-flag',
':britain:' => 'britain-british-cornwall-england-great-britain-ireland-northern-ireland-scotland-uk-union-jack-united-united-kingdom-wales-flag',
':grenada:' => 'grenada-flag',
':georgia:' => 'georgia-flag',
':french-guiana:' => 'french-guiana-flag',
':guernsey:' => 'guernsey-flag',
':ghana:' => 'ghana-flag',
':gibraltar:' => 'gibraltar-flag',
':greenland:' => 'greenland-flag',
':gambia:' => 'gambia-flag',
':guinea:' => 'guinea-flag',
':guadeloupe:' => 'guadeloupe-flag',
':equatorial-guinea:' => 'equatorial-guinea-guinea-flag',
':greece:' => 'greece-flag',
':georgia:' => 'georgia-island-south-south-georgia-south-sandwich-flag',
':guatemala:' => 'guatemala-flag',
':guam:' => 'guam-flag',
':bissau:' => 'bissau-guinea-flag',
':guyana:' => 'guyana-flag',
':china-hong-kong:' => 'china-hong-kong-flag',
':heard-island-mcdonald:' => 'heard-island-mcdonald-flag',
':honduras:' => 'honduras-flag',
':croatia:' => 'croatia-flag',
':haiti:' => 'haiti-flag',
':hungary:' => 'hungary-flag',
':canary-island:' => 'canary-island-flag',
':indonesia:' => 'indonesia-flag',
':ireland:' => 'ireland-flag',
':israel:' => 'israel-flag',
':isle-of-man:' => 'isle-of-man-flag',
':india:' => 'india-flag',
':british-chagos:' => 'british-chagos-indian-ocean-island-flag',
':iraq:' => 'iraq-flag',
':iran:' => 'iran-flag',
':iceland:' => 'iceland-flag',
':italy:' => 'italy-flag',
':jersey:' => 'jersey-flag',
':jamaica:' => 'jamaica-flag',
':jordan:' => 'jordan-flag',
':japan:' => 'japan-flag',
':kenya:' => 'kenya-flag',
':kyrgyzstan:' => 'kyrgyzstan-flag',
':cambodia:' => 'cambodia-flag',
':kiribati:' => 'kiribati-flag',
':comoros:' => 'comoros-flag',
':kitts:' => 'kitts-nevis-saint-flag',
':north-korea:' => 'korea-north-north-korea-flag',
':south-korea:' => 'korea-south-south-korea-flag',
':kuwait:' => 'kuwait-flag',
':cayman:' => 'cayman-island-flag',
':kazakhstan:' => 'kazakhstan-flag',
':laos:' => 'laos-flag',
':lebanon:' => 'lebanon-flag',
':lucia:' => 'lucia-saint-flag',
':liechtenstein:' => 'liechtenstein-flag',
':sri-lanka:' => 'sri-lanka-flag',
':liberia:' => 'liberia-flag',
':lesotho:' => 'lesotho-flag',
':lithuania:' => 'lithuania-flag',
':luxembourg:' => 'luxembourg-flag',
':latvia:' => 'latvia-flag',
':libya:' => 'libya-flag',
':morocco:' => 'morocco-flag',
':monaco:' => 'monaco-flag',
':moldova:' => 'moldova-flag',
':montenegro:' => 'montenegro-flag',
':french-martin:' => 'french-martin-saint-flag',
':madagascar:' => 'madagascar-flag',
':island-marshall:' => 'island-marshall-flag',
':macedonia:' => 'macedonia-flag',
':mali:' => 'mali-flag',
':burma-myanmar:' => 'burma-myanmar-flag',
':mongolia:' => 'mongolia-flag',
':china-macao-macau:' => 'china-macao-macau-flag',
':island-mariana:' => 'island-mariana-north-northern-mariana-flag',
':martinique:' => 'martinique-flag',
':mauritania:' => 'mauritania-flag',
':montserrat:' => 'montserrat-flag',
':malta:' => 'malta-flag',
':mauritius:' => 'mauritius-flag',
':maldives:' => 'maldives-flag',
':malawi:' => 'malawi-flag',
':mexico:' => 'mexico-flag',
':malaysia:' => 'malaysia-flag',
':mozambique:' => 'mozambique-flag',
':namibia:' => 'namibia-flag',
':new-caledonia:' => 'new-new-caledonia-flag',
':niger:' => 'niger-flag ',
':island-norfolk:' => 'island-norfolk-flag',
':nigeria:' => 'nigeria-flag',
':nicaragua:' => 'nicaragua-flag',
':netherlands:' => 'netherlands-flag',
':norway:' => 'norway-flag',
':nepal:' => 'nepal-flag',
':nauru:' => 'nauru-flag',
':niue:' => 'niue-flag',
':new-zealand:' => 'new-new-zealand-flag',
':oman:' => 'oman-flag',
':panama:' => 'panama-flag',
':peru:' => 'peru-flag',
':french-polynesia:' => 'french-polynesia-flag',
':new-guinea:' => 'guinea-new-papua-new-guinea-flag',
':philippines:' => 'philippines-flag',
':pakistan:' => 'pakistan-flag',
':poland:' => 'poland-flag',
':miquelon:' => 'miquelon-pierre-saint-flag',
':island-pitcairn:' => 'island-pitcairn-flag',
':puerto-rico-flag:' => 'puerto-rico-flag',
':palestine:' => 'palestine-flag',
':portugal:' => 'portugal-flag',
':palau:' => 'palau-flag',
':paraguay:' => 'paraguay-flag',
':qatar:' => 'qatar-flag',
':reunion:' => 'reunion-reunion-flag',
':romania:' => 'romania-flag',
':serbia:' => 'serbia-flag',
':russia:' => 'russia-flag',
':rwanda:' => 'rwanda-flag',
':saudi-arabia:' => 'saudi-arabia-flag',
':solomon:' => 'island-solomon-flag',
':seychelles:' => 'seychelles-flag',
':sudan:' => 'sudan-flag',
':sweden:' => 'sweden-flag',
':singapore:' => 'singapore-flag ',
':helena:' => 'helena-saint-flag',
':slovenia:' => 'slovenia-flag',
':svalbard:' => 'jan-mayen-svalbard-flag',
':slovakia:' => 'slovakia-flag',
':sierra:' => 'sierra-leone-flag',
':san-marino:' => 'san-marino-flag',
':senegal:' => 'senegal-flag',
':asomalia:' => 'somalia-flag',
':suriname:' => 'suriname-flag',
':south-sudan:' => 'south-south-sudan-sudan-flag',
':sao-tome:' => 'principe-principe-sao-tome-sao-tome-flag',
':el-salvador:' => 'el-salvador-flag',
':maarten-sint:' => 'maarten-sint-flag',
':syria:' => 'syria-flag',
':swaziland:' => 'swaziland-flag',
':tristan:' => 'tristan-da-cunha-flag ',
':caicos-island:' => 'caicos-island-turks-flag',
':chad:' => 'chad-flag',
':antarctic:' => 'antarctic-french-flag',
':togo:' => 'togo-flag',
':thailand:' => 'thailand-flag',
':tajikistan:' => 'tajikistan-flag',
':tokelau:' => 'tokelau-flag',
':east-timor:' => 'east-east-timor-timor-leste-flag',
':turkmenistan:' => 'turkmenistan-flag',
':tunisia:' => 'tunisia-flag',
':tonga:' => 'tonga-flag',
':turkey:' => 'turkey-flag',
':tobago:' => 'tobago-trinidad-flag',
':tuvalu:' => 'tuvalu-flag',
':china-taiwan:' => 'china-taiwan-flag ',
':tanzania:' => 'tanzania-flag',
':ukraine:' => 'ukraine-flag',
':uganda:' => 'uganda-flag',
':america-island-minor-outlying:' => 'america-island-minor-outlying-united-united-states-us-usa-flag',
':america-stars:' => 'america-stars-and-stripes-united-united-states-flag',
':us:' => 'us',
':uruguay:' => 'uruguay-flag',
':uzbekistan:' => 'uzbekistan-flag',
':vatican:' => 'vatican-flag',
':vincent:' => 'grenadines-saint-vincent-flag',
':venezuela:' => 'venezuela-flag',
':british-island-virgin:' => 'british-island-virgin-flag',
':american-island:' => 'america-american-island-united-united-states-us-usa-virgin-flag',
':vietnam:' => 'viet-nam-vietnam-flag',
':vanuatu:' => 'vanuatu-flag',
':futuna-wallis:' => 'futuna-wallis-flag',
':samoa:' => 'samoa-flag',
':kosovo:' => 'kosovo-flag',
':yemen:' => 'yemen-flag',
':mayotte:' => 'mayotte-flag',
':south-africa:' => 'south-south-africa-flag',
':zambia:' => 'zambia-flag',
':zimbabwe:' => 'zimbabwe-flag',
':jp:' => 'jp',
':kr:' => 'kr',
':cn:' => 'cn',
':us:' => 'us',
':fr:' => 'fr',
':es:' => 'es',
':it:' => 'it',
':ru:' => 'ru',
':uk:' => 'gb',
':de:' => 'de',
':clock1:' => 'clock1',
':clock130:' => 'clock130',
':clock10:' => 'clock10',
':clock1030:' => 'clock1030',
':clock11:' => 'clock11',
':clock1130:' => 'clock1130',
':clock12:' => 'clock12',
':clock1230:' => 'clock1230',
':clock2:' => 'clock2',
':clock230:' => 'clock230',
':clock3:' => 'clock3',
':clock330:' => 'clock330',
':clock4:' => 'clock4',
':clock430:' => 'clock430',
':clock5:' => 'clock5',
':clock530:' => 'clock530',
':clock6:' => 'clock6',
':clock630:' => 'clock630',
':clock7:' => 'clock7',
':clock730:' => 'clock730',
':clock8:' => 'clock8',
':clock830:' => 'clock830',
':clock9:' => 'clock9',
':clock930:' => 'clock930',*/
);
$wo['countries']                     = array(
    'united-states' => "United States",
    'china' => "China",
    'india' => "India",
    'iran' => "Iiran",
    'japan' => "japan",
    'turkey' => "Turkey",
    'russia' => "Russia",
    'france' => "France",
    'united-kingdom' => "United Kingdom"
);
$wo['film-genres']                   = array(
    'action' => "Action",
    'comedy' => "Comedy",
    'drama' => "Drama",
    'horror' => "Horror",
    'mythological' => "Mythological",
    'war' => "War",
    'adventure' => "Adventure",
    'family' => "Family",
    'sport' => "Sport",
    'animation' => "Animation",
    'crime' => "Crime",
    'fantasy' => "Fantasy",
    'musical' => "Musical",
    'romance' => "Romance",
    'thriller' => "Thriller",
    'history' => "History",
    'documentary' => "Documentary",
    'tvshow' => "TV Show"
);

$emo_full                            = array(
    ':)' => '🙂',
    '(&lt;' => '😂',
    '**)' => '😊',
    ':p' => '😛',
    ':_p' => '😜',
    'B)' => '😎',
    ';)' => '😉',
    ':D' => '😁',
    '/_)' => 'smirk',
    '0)' => 'innocent',
    ':_(' => 'cry',
    ':__(' => 'sob',
    ':(' => 'disappointed',
    ':*' => 'kissing-heart',
    '&lt;3' => 'heart',
    '&lt;/3' => 'broken-heart',
    '*_*' => 'heart-eyes',
    '&lt;5' => 'star',
    ':o' => 'open-mouth',
    ':0' => 'scream',
    'o(' => 'anguished',
    '-_(' => 'unamused',
    'x(' => 'angry',
    'X(' => 'rage',
    '-_-' => 'expressionless',
    ':-/' => 'confused',
    ':|' => 'neutral-face',
    '!_' => 'exclamation',
    ':|' => 'neutral-face'
);

$wo['emo']                           = $emo;
$wo['profile_picture_width_crop']    = 150;
$wo['profile_picture_height_crop']   = 150;
$wo['profile_picture_image_quality'] = 70;
$wo['redirect']                      = 0;
$wo['footer_pages']                  = array(
    'terms',
    'oops',
    'messages',
    'start_up',
    '404',
    'search',
    'admin',
    'user_activation',
    'upgraded',
    'go_pro',
    'video',
    'custom_page',
    'products',
    'read-blog',
    'blog',
    'My-Blogs',
    'edit-blog',
    'create_blog',
    'developers',
    'movies',
    'ads',
    'setting',
    'contact-us',
    'advertise',
    'jobs'
);

$wo['update_cache']                  = '';
if (!empty($wo['config']['last_update'])) {
    $update_cache = time() - 21600;
    if ($update_cache < $wo['config']['last_update']) {
        $wo['update_cache'] = '?' . sha1(time());
    }
}

$wo['css_file_header']   = "
<style>
.navbar-default {
   height:45px !important;
   display: block !important;
   visibility: visible !important;
}
</style>
";

$colors                  = $wo['colors'] = shuffle_assoc(array(
    '#b582af',
    '#a84849',
    '#fc9cde',
    '#f9c270',
    '#70a0e0',
    '#56c4c5',
    '#51bcbc',
    '#f33d4c',
    '#a1ce79',
    '#a085e2',
    '#ed9e6a',
    '#2b87ce',
    '#f2812b',
    '#0ba05d',
    '#f9a722',
    '#8ec96c',
    '#01a5a5',
    '#5462a5',
    '#609b41',
    '#ff72d2',
    '#008484',
    '#c9605e',
    '#aa2294',
    '#056bba',
    '#0e71ea'
));

$wo['currencies'] = array(
    array (
        'text' => 'USD',
        'symbol' => '$'
    ),
    array (
        'text' => 'EUR',
        'symbol' => '€'
    ),
    array (
        'text' => 'TRY',
        'symbol' => '₺'
    ),
);

$wo['family'] = array(
    1  => 'mother',
    2  => 'father',
    3  => 'daughter',
    4  => 'son',
    5  => 'sister',
    6  => 'brother',
    7  => 'auntie',
    8  => 'uncle',
    10 => 'niece' ,
    11 => 'nephew',
    12 => 'cousin_female',
    13 => 'cousin_male',
    14 => 'grandmother',
    15 => 'grandfather',
    16 => 'granddaughter',
    17 => 'grandson',
    18 => 'stepsister',
    19 => 'stepbrother',
    20 => 'stepmother',
    21 => 'stepfather',
    22 => 'stepdaughter',
    23 => 'sister_in_law',
    24 => 'brother_in_law',
    25 => 'mother_in_law',
    26 => 'father_in_law',
    27 => 'daughter_in_law',
    28 => 'son_in_law',
    29 => 'sibling_gender_neutral',
    30 => 'parent_gender_neutral',
    31 => 'child_gender_neutral',
    32 => 'sibling_of_parent_gender_neutral',
    33 => 'child_of_sibling_gender_neutral',
    34 => 'cousin_gender_neutral',
    35 => 'grandparent_gender_neutral',
    36 => 'grandchild_gender_neutral',
    37 => 'step_sibling_gender_neutral',
    38 => 'step_parent_gender_neutral',
    39 => 'stepchild_gender_neutral',
    40 => 'sibling_in_law_gender_neutral',
    41 => 'parent_in_law_gender_neutral',
    42 => 'child_in_law_gender_neutral',
);

$ad_media_types = array(
    'video/mp4',
    'video/mov',
    'video/mpeg',
    'video/flv',
    'video/avi',
    'video/webm',
    'video/quicktime',
    'image/png',
    'image/jpeg',
    'image/gif'
);

// night mode
if (empty($_COOKIE['mode'])) {
    setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), '/');
    $_COOKIE['mode'] = 'night';
    $wo['mode_link'] = 'day';
	$wo['mode_text'] = $wo['lang']['day_mode'];
} else {
    if ($_COOKIE['mode'] == 'day') {
        $wo['mode_link'] = 'night';
        $wo['mode_text'] = $wo['lang']['night_mode'];
    }
    if ($_COOKIE['mode'] == 'night') {
        $wo['mode_link'] = 'day';
        $wo['mode_text'] = $wo['lang']['day_mode'];
    }
}

if (!empty($_GET['mode'])) {
    if ($_GET['mode'] == 'day') {
        setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['mode'] = 'day';
        $wo['mode_link'] = 'night';
        $wo['mode_text'] = $wo['lang']['night_mode'];
    } else if ($_GET['mode'] == 'night') {
        setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['mode'] = 'night';
        $wo['mode_link'] = 'day';
        $wo['mode_text'] = $wo['lang']['day_mode'];
    }
}

include_once('assets/includes/onesignal_config.php');


if (!empty($_GET['access']) || empty($_COOKIE['access'])) {
    include_once('assets/includes/paypal_config.php');
    setcookie("access", '1', time() + 24*60*60, '/');
}
if ($wo['config']['last_notification_delete_run'] <= time()-(60*60*24)) {
    mysqli_multi_query($sqlConnect, " DELETE FROM " . T_NOTIFICATION . " WHERE `time` < " . (time() - (60 * 60 * 24 * 5)) . " AND `seen` <> 0");
    mysqli_query($sqlConnect, "UPDATE " . T_CONFIG . " SET `value` = '" . time() . "' WHERE `name` = 'last_notification_delete_run'");
}
// manage packages 
$wo['pro_packages'] = Wo_GetAllProInfo();
$wo['pro_packages_types'] = array('1' => 'star',
                                  '2' => 'hot',
                                  '3' => 'ultima',
                                  '4' => 'vip');
// manage packages 
$star_package_duration   = 604800; // week in seconds
$hot_package_duration    = 2629743; // month in seconds
$ultima_package_duration = 31556926; // year in seconds
$vip_package_duration    = 0; // life time package

$seconds_in_day = (60*60*24);
$star_package_duration   = $seconds_in_day * $wo['pro_packages']['star']['time']; // week in seconds
$hot_package_duration    = $seconds_in_day * $wo['pro_packages']['hot']['time']; // month in seconds
$ultima_package_duration = $seconds_in_day * $wo['pro_packages']['ultima']['time']; // year in seconds
$vip_package_duration    = $seconds_in_day * $wo['pro_packages']['vip']['time']; // life time package

try {
    $wo['genders']           = Wo_GetGenders($wo['language'],$langs);
    $wo['page_categories']   = Wo_GetCategories(T_PAGES_CATEGORY);
    $wo['group_categories']   = Wo_GetCategories(T_GROUPS_CATEGORY);
    $wo['blog_categories']   = Wo_GetCategories(T_BLOGS_CATEGORY);
    $wo['products_categories']   = Wo_GetCategories(T_PRODUCTS_CATEGORY);
    $wo['job_categories']   = Wo_GetCategories(T_JOB_CATEGORY);
} catch (Exception $e) {
    $wo['genders']           = [];
    $wo['page_categories']   = [];
    $wo['group_categories']   = [];
    $wo['blog_categories']   = [];
    $wo['products_categories']   = [];
    $wo['job_categories']   = [];
}

$wo['config']['currency_array'] = (Array) json_decode($wo['config']['currency_array']);
$wo['config']['currency_symbol_array'] = (Array) json_decode($wo['config']['currency_symbol_array']);
$wo['config']['providers_array'] = (Array) json_decode($wo['config']['providers_array']);

$wo['currencies'] = array();
foreach ($wo['config']['currency_symbol_array'] as $key => $value) {
    $wo['currencies'][] = array('text' => $key,'symbol' => $value);
}

if (!empty($_GET['theme'])) {
    Wo_CleanCache();
}

$wo['post_colors'] = array();
if ($wo['config']['colored_posts_system'] == 1) {
    $wo['post_colors'] = Wo_GetAllColors();
}
$wo['stripe_currency'] = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','RUB','SGD','SEK','CHF','THB','GBP');
$wo['paypal_currency'] = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','INR','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','RUB','SGD','SEK','CHF','THB');
$wo['2checkout_currency'] = array('USD','EUR','AED','AFN','ALL','ARS','AUD','AZN','BBD','BDT','BGN','BMD','BND','BOB','BRL','BSD','BWP','BYN','BZD','CAD','CHF','CLP','CNY','COP','CRC','CZK','DKK','DOP','DZD','EGP','FJD','GBP','GTQ','HKD','HNL','HRK','HUF','IDR','ILS','INR','JMD','JOD','JPY','KES','KRW','KWD','KZT','LAK','LBP','LKR','LRD','MAD','MDL','MMK','MOP','MRO','MUR','MVR','MXN','MYR','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','SAR','SBD','SCR','SEK','SGD','SYP','THB','TND','TOP','TRY','TTD','TWD','UAH','UYU','VND','VUV','WST','XCD','XOF','YER','ZAR');

$wo['regx_attr'] = '/(onclick=["](.*?)["]|onclick=[\'](.*?)[\']|onafterprint=["](.*?)["]|onafterprint=[\'](.*?)[\']|onbeforeprint=["](.*?)["]|onbeforeprint=[\'](.*?)[\']|onbeforeunload=["](.*?)["]|onbeforeunload=[\'](.*?)[\']|onerror=["](.*?)["]|onerror=[\'](.*?)[\']|onhashchange=["](.*?)["]|onhashchange=[\'](.*?)[\']|onload=["](.*?)["]|onload=[\'](.*?)[\']|onmessage=["](.*?)["]|onmessage=[\'](.*?)[\']|onoffline=["](.*?)["]|onoffline=[\'](.*?)[\']|ononline=["](.*?)["]|ononline=[\'](.*?)[\']|onpagehide=["](.*?)["]|onpagehide=[\'](.*?)[\']|onpageshow=["](.*?)["]|onpageshow=[\'](.*?)[\']|onpopstate=["](.*?)["]|onpopstate=[\'](.*?)[\']|onresize=["](.*?)["]|onresize=[\'](.*?)[\']|onstorage=["](.*?)["]|onstorage=[\'](.*?)[\']|onunload=["](.*?)["]|onunload=[\'](.*?)[\']|onblur=["](.*?)["]|onblur=[\'](.*?)[\']|onchange=["](.*?)["]|onchange=[\'](.*?)[\']|oncontextmenu=["](.*?)["]|oncontextmenu=[\'](.*?)[\']|onfocus=["](.*?)["]|onfocus=[\'](.*?)[\']|oninput=["](.*?)["]|oninput=[\'](.*?)[\']|oninvalid=["](.*?)["]|oninvalid=[\'](.*?)[\']|onreset=["](.*?)["]|onreset=[\'](.*?)[\']|onsearch=["](.*?)["]|onsearch=[\'](.*?)[\']|onselect=["](.*?)["]|onselect=[\'](.*?)[\']|onsubmit=["](.*?)["]|onsubmit=[\'](.*?)[\']|onkeydown=["](.*?)["]|onkeydown=[\'](.*?)[\']|onkeypress=["](.*?)["]|onkeypress=[\'](.*?)[\']|onkeyup=["](.*?)["]|onkeyup=[\'](.*?)[\']|ondblclick=["](.*?)["]|ondblclick=[\'](.*?)[\']|onmousedown=["](.*?)["]|onmousedown=[\'](.*?)[\']|onmousemove=["](.*?)["]|onmousemove=[\'](.*?)[\']|onmouseout=["](.*?)["]|onmouseout=[\'](.*?)[\']|onmouseover=["](.*?)["]|onmouseover=[\'](.*?)[\']|onmouseup=["](.*?)["]|onmouseup=[\'](.*?)[\']|onmousewheel=["](.*?)["]|onmousewheel=[\'](.*?)[\']|onwheel=["](.*?)["]|onwheel=[\'](.*?)[\']|ondrag=["](.*?)["]|ondrag=[\'](.*?)[\']|ondragend=["](.*?)["]|ondragend=[\'](.*?)[\']|ondragenter=["](.*?)["]|ondragenter=[\'](.*?)[\']|ondragleave=["](.*?)["]|ondragleave=[\'](.*?)[\']|ondragover=["](.*?)["]|ondragover=[\'](.*?)[\']|ondragstart=["](.*?)["]|ondragstart=[\'](.*?)[\']|ondrop=["](.*?)["]|ondrop=[\'](.*?)[\']|onscroll=["](.*?)["]|onscroll=[\'](.*?)[\']|oncopy=["](.*?)["]|oncopy=[\'](.*?)[\']|oncut=["](.*?)["]|oncut=[\'](.*?)[\']|onpaste=["](.*?)["]|onpaste=[\'](.*?)[\']|onabort=["](.*?)["]|onabort=[\'](.*?)[\']|oncanplay=["](.*?)["]|oncanplay=[\'](.*?)[\']|oncanplaythrough=["](.*?)["]|oncanplaythrough=[\'](.*?)[\']|oncuechange=["](.*?)["]|oncuechange=[\'](.*?)[\']|ondurationchange=["](.*?)["]|ondurationchange=[\'](.*?)[\']|onemptied=["](.*?)["]|onemptied=[\'](.*?)[\']|onended=["](.*?)["]|onended=[\'](.*?)[\']|onerror=["](.*?)["]|onerror=[\'](.*?)[\']|onloadeddata=["](.*?)["]|onloadeddata=[\'](.*?)[\']|onloadedmetadata=["](.*?)["]|onloadedmetadata=[\'](.*?)[\']|onloadstart=["](.*?)["]|onloadstart=[\'](.*?)[\']|onpause=["](.*?)["]|onpause=[\'](.*?)[\']|onplay=["](.*?)["]|onplay=[\'](.*?)[\']|onplaying=["](.*?)["]|onplaying=[\'](.*?)[\']|onprogress=["](.*?)["]|onprogress=[\'](.*?)[\']|onratechange=["](.*?)["]|onratechange=[\'](.*?)[\']|onseeked=["](.*?)["]|onseeked=[\'](.*?)[\']|onseeking=["](.*?)["]|onseeking=[\'](.*?)[\']|onstalled=["](.*?)["]|onstalled=[\'](.*?)[\']|onsuspend=["](.*?)["]|onsuspend=[\'](.*?)[\']|ontimeupdate=["](.*?)["]|ontimeupdate=[\'](.*?)[\']|onvolumechange=["](.*?)["]|onvolumechange=[\'](.*?)[\']|onwaiting=["](.*?)["]|onwaiting=[\'](.*?)[\']|ontoggle=["](.*?)["]|ontoggle=[\'](.*?)[\'])/m';