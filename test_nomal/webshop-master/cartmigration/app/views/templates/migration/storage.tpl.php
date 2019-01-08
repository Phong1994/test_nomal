<?php
$notice = $this->getGlobal('notice');
?>
<form action="" method="post" id="form-storage">
    <input type="hidden" name="process" value="storageOrSetup"/>
    <input type="hidden" name="current" value="storage"/>
    <div class="panel">
        <div class="panel-body">
            <div>Importing data files into temporary storage for later processing …</div>
            <div class="console-wrap">
                <div class="console-title">Console</div>
                <div class="console-log" id="console-log-storage"><?php echo $notice['src']['storage']['msg']; ?></div>
            </div>
        </div>
        <div class="form-submit text-center">
            <div id="btn-retry-storage-wrap"><a href="javascript:void(0)" class="btn-submit" id="btn-retry-storage" style="display: none;">Retry</a></div>
        </div>
        <div class="clearfix"></div>
    </div>
</form>
