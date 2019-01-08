<form action="" method="post" id="form-install-database">
    <input type="hidden" name="<?php echo WEB_URI;?>"  value="setup">
    <input type="hidden" name="action" value="installDatabase"/>
    <div class="console-wrap">
        <div class="console-title">Console</div>
        <div class="console-log" id="console-log-install">
            <p class="console-success">- Install database ...</p>
        </div>
    </div>
    <div class="form-submit text-center display-none" id="form-install-retry">
        <div id="btn-retry-install-wrap" class="btn btn-submit">Retry</div>
    </div>
    <div class="form-install-submit">
        <div class="form-loading" id="form-loading"><img
                    src="<?php echo Bootstrap::getUrl('pub/image/help/loader-large.gif'); ?>"/> Connecting ...
        </div>
        <div class="btn btn-install btn-submit display-none" id="form-install-db-submit">Next</div>
    </div>
    <div class="clearfix"></div>
</form>