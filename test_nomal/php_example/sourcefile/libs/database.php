<?php
$conn = null;
function db_connect(){
    global $conn;
    if (!$conn){
        $conn = mysqli_connect('localhost','root','','php_example')
        or die("Khong ket noi duoc DB");
        mysqli_set_charset($conn,'UTF-8');
    }
}
function db_close(){
    global $conn;
    if($conn){
        mysqli_close($conn);
    }
}
//Lay danh sach, ket qua tra ve cac record trong mot mang
function db_get_list($sql){
    db_connect();
    global $conn;
    $data = array();
    $result = mysqli_query($conn,$sql);
    while ($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    return $data;
}
//ham lay chi tiet, select theo id
function db_get_row($sql){
    db_connect();
    global $conn;
    $result = mysqli_query($conn,$sql);
    $row = array();
    if (mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
    }
    return $row;
}
//ham thuc thi cau truy van
function db_excute($sql){
    global  $conn;
    db_connect();
    return mysqli_query($conn,$sql);
}