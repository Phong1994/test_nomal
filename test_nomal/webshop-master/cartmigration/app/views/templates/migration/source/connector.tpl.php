<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['src']['cart_type'];
$guide_path = Bootstrap::getTemplate('migration/source/connector/' . $cart_type . '.tpl');
$guide_path_default = Bootstrap::getTemplate('migration/source/connector/default.tpl');
?>
<?php //
//if(file_exists($guide_path)){
//    include $guide_path;
//}else{
//    include $guide_path_default;
//}
//?>
<div class="form-group" id="form-source-token">
    <div class="col-md-3"><label class="pull-right">Source Token <span>*</span></label></div>
    <div class="col-md-9">
        <div class="col-lg-12"><input type="text" class="form-control required" name="source_token" id="source-token"/></div>
    </div>
    <div style="clear: both;"></div>
</div>