<form action="" method="post" id="form-setup-web">

    <div class="form-group row border-bottom">
        <div class="col-md-3 required">Admin Url</div>
        <div class="col-md-9" id="setup-admin-url">
            <span class=""><?php echo Bootstrap::getUrl().'/';?></span>
            <input type="text" name="admin_url" class="form-control "  required value="<?php echo Bootstrap::getConfig('admin_url')?>"/>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-3">First Name</div>
        <div class="col-md-5">
            <input type="text" name="admin_first_name" class="form-control" value=""/>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-3">Last Name</div>
        <div class="col-md-5">
            <input type="text" name="admin_last_name" class="form-control" value=""/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 required" >Create admin account</div>
        <div class="col-md-5">
            <input type="text" name="admin_account" class="form-control " required value=""/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 required">Create admin password</div>
        <div class="col-md-5">
            <input type="password" id="admin_password" name="admin_password" class="form-control valid_length" required value=""/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 required">Retype password</div>
        <div class="col-md-5">
            <input type="text" id="admin_repassword" name="admin_repassword" class="form-control " required value=""/>
            <span style="color: red" id="admin_repassword_error"></span>
        </div>
    </div>

    <input type="hidden" name="<?php echo WEB_URI;?>"  value="setup">
    <input type="hidden" name="action"  value="createAdminAccount">
    <div class="form-install-submit">
        <div class="form-loading " id="form-loading"><img
                src="<?php echo Bootstrap::getUrl('pub/image/help/loader-large.gif'); ?>"/> Connecting ...
        </div>
        <div class="btn btn-install btn-submit" id="form-setup-web-submit">Next</div>
    </div>
</form>