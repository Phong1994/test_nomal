<?php
/**
 * Created by PhpStorm.
 * User: Phong
 * Date: 5/15/2018
 * Time: 4:50 PM
 */
require './libs/students.php';

// Thực hiện xóa
$id = isset($_POST['id']) ? (int)$_POST['id'] : '';
if ($id){
    delete_student($id);
}

// Trở về trang danh sách
header("location: student-list.php");
