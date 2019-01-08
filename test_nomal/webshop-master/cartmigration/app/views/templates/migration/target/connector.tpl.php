<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['target']['cart_type'];
if(!$cart_type){
    $cart_type = $this->getFirstTargetCartType();
}
$guide_path = Bootstrap::getTemplate('migration/target/connector/' . $cart_type . '.tpl');
?>
<?php //if(file_exists($guide_path)) include $guide_path; ?>
<div class="form-group" id="form-target-token">
    <div class="col-md-3"><label class="pull-right">Target Token <span>*</span></label></div>
    <div class="col-md-9">
        <div class="col-lg-12"><input type="text" class="form-control required" name="target_token" id="target-token"/></div>
    </div>
    <div style="clear: both;"></div>
</div>