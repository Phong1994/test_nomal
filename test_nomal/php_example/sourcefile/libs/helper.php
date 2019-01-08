<?php
function base_url($uri = ''){
    return 'http://localhost:1001/test_nomal/php_example/sourcefile/'.$uri;
}

function redirect($url){
    header("Location: {$url}");
}
function input_post($key){
    return isset($_POST[$key])? trim($_POST[$key]) : false;
}

function input_get($key){
    return isset($_GET[$key])? trim($_GET[$key]) : false;
}

function is_submit($key){
    return (isset($_POST['request_name']) && $_POST['request_name'] == $key);
}

function show_error($error,$key){
    echo '<span style="color:red">'.(empty($error[$key]) ? "" : $error[$key]).'</span>' ;
}
