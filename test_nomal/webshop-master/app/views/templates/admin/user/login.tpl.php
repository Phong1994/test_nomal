<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07/03/2018
 * Time: 11:56
 */
    $class_name = get_class($this);
    if($class_name == 'Controller_Admin_Login'){
        $is_login = true;

    }else{
        $is_login = false;
    }
?>
<form class="form-admin-login" action="" method="post" id="form-admin-login">
    <div class="admin-login-title">Webshop</div>
    <?php
        $error = $this->getError();
        if($error):
    ?>
    <div class="admin-login-error row">
        <div class="col-md-2">
            <i class="fa fa-times" style="color: red"></i>
        </div>
        <p class="col-md-10" id="admin-login-message">
            <?php echo $error?>
        </p>
    </div>
    <?php else:?>

            <p class="text-center admin-login-welcome">Welcome, please sign in</p>
    <?php endif;?>
    <div class="admin-login-content">
        <div class="form-group">
            <p class="required">Username</p>
            <input type="text" class="form-control" required name="username">
        </div>
        <div class="form-group">
            <p class="required">Password</p>
            <input type="password" class="form-control" required name="password">
        </div>
        <div>
            <a href="<?php echo Bootstrap::getUrlAdmin('user/forgot');?>">Forgot your password?</a>
        </div>
    </div>
    <div class="admin-login-submit">
        <div class="form-loading" id="form-loading"><img
                src="<?php echo Bootstrap::getImages('help/loader-large.gif'); ?>"/> Connecting ...
        </div>
        <button type="submit" class="btn btn-install btn-submit  " id="admin-login-submit">Sign in</button>
    </div>
    <input type="hidden" value="login" name="action">
</form>
