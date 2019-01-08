<?php
$conn = mysqli_connect('localhost', 'root', '', 'demo') or die ('Không thể kết nối tới database');

// Câu truy vấn
$sql = 'SELECT * FROM customer';

// Thực hiện câu truy vấn, hàm này truyền hai tham số vào là biến kết nối và câu truy vấn
$result = mysqli_query($conn, $sql);

// Nếu thực thi không được thì thông báo truy vấn bị sai
if (!$result){
    die ('Câu truy vấn bị sai');
}

// Lặp qua kết quả và in ra ngoài màn hình
// Vì các field trong database là id, name, phone, address nên
// khi vardum mang sẽ có cấu trúc tương tự
while ($row = mysqli_fetch_assoc($result)){
    var_dump($row);
}

// Xóa kết quả khỏi bộ nhớ
mysqli_free_result($result);

// Sau khi thực thi xong thì ngắt kết nối database
mysqli_close($conn);
?>