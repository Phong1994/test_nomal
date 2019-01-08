<?php
$error = array();
include ($_SERVER['DOCUMENT_ROOT'] . '/php_example/sourcefile/libs/helper.php');
require $_SERVER['DOCUMENT_ROOT'].'/php_example/sourcefile/libs/role.php';
// BƯỚC 1: KIỂM TRA NẾU LÀ ADMIN THÌ REDIRECT
if (is_admin()){
    redirect(base_url('admin/?m=common&a=dashboard'));
}

// BƯỚC 2: NẾU NGƯỜI DÙNG SUBMIT FORM
if (is_submit('login'))
{
    // lấy tên đăng nhập và mật khẩu
    $username = input_post('username');
    $password = input_post('password');

    // Kiểm tra tên đăng nhập
    if (empty($username)){
        $error['username'] = 'Bạn chưa nhập tên đăng nhập';
    }

    // Kiểm tra mật khẩu
    if (empty($password)){
        $error['password'] = 'Bạn chưa nhập mật khẩu';
    }

    // Nếu không có lỗi
    if (!$error)
    {
        // include file xử lý database user
        include_once('database/user.php');

        // lấy thông tin user theo username
        $user = db_user_get_by_username($username);

        // Nếu không có kết quả
        if (empty($user)){
            $error['username'] = 'Tên đăng nhập không đúng';
        }
        // nếu có kết quả nhưng sai mật khẩu
        else if ($user['password'] != md5($password)){
            $error['password'] = 'Mật khẩu bạn nhập không đúng';
        }

        // nếu mọi thứ ok thì tức là đăng nhập thành công
        // nên thực hiện redirect sang trang chủ
        if (!$error){
            set_logged($user['username'], $user['level']);
            redirect(base_url('admin/?m=common&a=dashboard'));
        }
    }
}

?>

<?php include_once('widgets/header.php'); ?>
    <h1>Trang đăng nhập!</h1>
    <form method="post" action="<?php echo base_url('admin/?m=common&a=login'); ?>">
        <table>
            <tr>
                <td>Username</td>
                <td>
                    <input type="text" name="username" value=""/>
                    <?php show_error($error, 'username'); ?>
                </td>
            </tr>
            <tr>
                <td>Password</td>
                <td>
                    <input type="password" name="password" value=""/>
                    <?php show_error($error, 'password'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="request_name" value="login"/>
                </td>
                <td>
                    <input type="submit" name="login-btn" value="Đăng nhập"/>
                </td>
            </tr>
        </table>
    </form>
<?php include_once('widgets/footer.php'); ?>