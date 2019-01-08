<?php if (!defined('IN SITE')) die ('The request not found');
function db_user_get_by_username($username){
    $username = addslashes($username);
    $sql = "SELECT * FROM tb_user WHERE username ='{username}'";
    return db_get_row($sql);
}