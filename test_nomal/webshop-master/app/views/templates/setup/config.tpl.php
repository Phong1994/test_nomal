<form action="" method="post" id="form-config-db">
    <div class="form-group row border-bottom">
        <div class="col-md-3 required">Database Host</div>
        <div class="col-md-5">
            <input type="text" name="host" class="form-control" required value="<?php echo Bootstrap::getConfig('db_host')?>"/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 required" >Database Username</div>
        <div class="col-md-5">
            <input type="text" name="username" class="form-control" required value="<?php echo Bootstrap::getConfig('db_username')?>"/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 ">Database password</div>
        <div class="col-md-5">
            <input type="password" name="password" class="form-control" value="<?php echo Bootstrap::getConfig('db_password')?>"/>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <div class="col-md-3 required">Database name</div>
        <div class="col-md-5">
            <input type="text" name="database" class="form-control" required value="<?php echo Bootstrap::getConfig('db_name')?>"/>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-3">Table Prefix</div>
        <div class="col-md-5">
            <input type="text" name="prefix" class="form-control" value="<?php echo Bootstrap::getConfig('db_prefix')?>"/>
        </div>
    </div>
    <input type="hidden" name="<?php echo WEB_URI;?>"  value="setup">
    <input type="hidden" name="action"  value="configDatabase">
    <div class="form-install-submit">
        <div class="form-loading" id="form-loading"><img
                    src="<?php echo Bootstrap::getUrl('pub/image/help/loader-large.gif'); ?>"/> Connecting ...
        </div>
    <div class="btn btn-install btn-submit" id="form-config-db-submit">Next</div>
    </div>
</form>