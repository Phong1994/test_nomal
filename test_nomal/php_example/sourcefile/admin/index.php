<?php
define('IN_SITE',true);

$module = isset($_GET['m'])? $_GET['m']:'';
$action = isset($_GET['a'])? $_GET['a']:'';
if (empty($module) || empty($action)){
    $module = 'common';
    $action = 'login';
}
$path = 'modules/'.$module.'/'.$action.'.php';
if(file_exists($path)){
    include_once('../libs/session.php');
    include_once ('../libs/role.php');
    include_once ('../libs/database.php');
    include_once ($path);
}
else{
    include_once ('modules/common/404.php');
}
