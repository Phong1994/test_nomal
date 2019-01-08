<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <title>Newspage Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

    <!-- Liên kết Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $_DOMAIN; ?>bootstrap/css/bootstrap.min.css"/>

    <!-- Liên kết thư viện jQuery -->
    <script src="<?php echo $_DOMAIN; ?>js/jquery.min.js"></script>
</head>
<body>
<?php

// Nếu chưa đăng nhập
if (!$user)
{
    echo
    '
            <div class="container">
                <div class="page-header">
                    <h1>Newspage <small>Administration</small></h1>
                </div><!-- div.page-header -->
            </div><!-- div.container -->
        ';
}
// Nếu đăng nhập
else
{
    echo
        '
            <nav class="navbar navbar-default" role="navigation">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="' . $_DOMAIN . '">Newspage Administration</a>
                    </div><!-- div.navbar-header -->
                </div><!-- div.container-fluid -->
            </nav>
        ';
}

?>