<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php echo $this->generateHeader(); ?>
</head>

<body >
<div class="container admin-page" >

    <div class="row admin-panel">
        <div class="col-md-2 admin-panel-menu"><?php include Bootstrap::getTemplate('admin/menu'); ?></div>
        <div class="col-md-10 admin-panel-content">
            <?php include Bootstrap::getTemplate('admin/header')?>
            <?php include Bootstrap::getTemplate('admin/user/info') ?>
        </div>
    </div>
</div>
</body>
</html>
