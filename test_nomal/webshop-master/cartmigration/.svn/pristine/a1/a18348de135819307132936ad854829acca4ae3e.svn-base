<?php
$type           = Bootstrap::getModel('type');
$sourceCartType = $type->sourceCarts();
$notice         = $this->getGlobal('notice');
$src_cart_type  = $notice['src']['cart_type'];
if (!$src_cart_type) {
    $src_cart_type = $this->getFirstSourceCartType();
}
$sourceCartSetup  = $this->setupSourceCart($src_cart_type);
$targetCartType   = $type->targetCarts();
$target_cart_type = $notice['target']['cart_type'];
if (!$target_cart_type) {
    $target_cart_type = $this->getFirstTargetCartType();
}
$targetCartSetup = $this->setupTargetCart($target_cart_type);
?>
<form action="" method="post" id="form-setup">
    <input id="form-setup-process" type="hidden" name="process" value="setupCart"/>
    <div class="panel source">
        <h3>Source Cart Setup</h3>
        <div class="panel-body">
            <div class="form-group" id="form-source-type">
                <div class="col-md-3"><label class="pull-right">Source Cart Type <span>*</span></label></div>
                <div class="col-md-9">
                    <div class="col-lg-6">
                        <select name="source_cart_type" class="form-control" id="source-cart-select">
                            <?php echo $this->toHtmlOption($sourceCartType, $notice['src']['cart_type']); ?>
                        </select>
                    </div>
                </div>
                <div class="clear-both"></div>
            </div>
            <div class="form-group" id="form-source-url">
                <div class="col-md-3"><label class="pull-right">Source Cart Url <span>*</span></label></div>
                <div class="col-md-9">
                    <div class="col-lg-12"><input type="text" class="form-control required url_http"
                                                  id="source-cart-url" name="source_cart_url"
                                                  value="<?php echo $notice['src']['cart_url']; ?>"/></div>
                </div>
                <div class="clear-both"></div>
            </div>
            <div id="source-info">
                <?php include $sourceCartSetup['view_path']; ?>
            </div>
        </div>
        <!--        <div class="form-submit text-center">-->
        <!--            <div class="form-loading" id="form-source-loading"><img-->
        <!--                        src="-->
        <?php //echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?><!--"/> Connecting ...-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="clear-both"></div>-->
    </div>

    <div class="panel target">
        <h3>Target Cart Setup</h3>
        <div class="panel-body">
            <div class="form-group" id="form-target-type">
                <div class="col-md-3"><label class="pull-right">Target Cart Type <span>*</span></label></div>
                <div class="col-md-9">
                    <div class="col-lg-6">
                        <select name="target_cart_type" class="form-control" id="target-cart-select">
                            <?php echo $this->toHtmlOption($targetCartType, $notice['target']['cart_type']); ?>
                        </select>
                    </div>
                </div>
                <div class="clear-both"></div>
            </div>
            <div class="form-group" id="form-target-url">
                <div class="col-md-3"><label class="pull-right">Target Cart Url <span>*</span></label></div>
                <div class="col-md-9">
                    <div class="col-lg-12"><input type="text" class="form-control required url_http"
                                                  id="target-cart-url" name="target_cart_url"
                                                  value="<?php echo $notice['target']['cart_url']; ?>"/></div>
                </div>
                <div class="clear-both"></div>
            </div>
            <div id="target-info">
                <?php include $targetCartSetup['view_path']; ?>
            </div>
        </div>

    </div>
    <div class="clear-both"></div>
    <div class="form-submit text-center">
        <div class="form-loading" id="form-loading"><img
                    src="<?php echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?>"/> Connecting ...
        </div>
        <div id="form-setup-submit-wrap"><a href="javascript:void(0)" class="btn-submit"
                                             id="form-setup-submit">Next</a>
        </div>
    </div>
</form>