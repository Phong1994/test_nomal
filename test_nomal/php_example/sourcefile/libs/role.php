<?php
function set_logged($username,$level){
    session_set('ss_user_token',array(
        'username' => $username,
        'level' => $level
    ));
}
function set_logout(){
    session_delete('ss_user_token');
}
function is_logged(){
    $user = session_get('ss_user_token');
    return $user;
}
function is_admin(){
    $user = is_logged();
    if (!empty($user['level']) && $user['level']== '1'){
        return true;
    }
    return false;
}

function get_current_username(){
$user  = is_logged();
    return isset($user['username']) ? $user['username'] : '';
}

// Lấy level người dùng hiện tại
function get_current_level(){
    $user  = is_logged();
    return isset($user['level']) ? $user['level'] : '';
}
