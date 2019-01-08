<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['target']['cart_type'];
if(!$cart_type){
    $cart_type = $this->getFirstTargetCartType();
}
$setupSourceCart = $this->setupTargetCart($cart_type);
$cart = Bootstrap::getModel($setupSourceCart['cart_model']);
$apiInfo = $cart->getApiInfo();
$guide_path = Bootstrap::getTemplate('migration/target/api/' . $cart_type . '.tpl');
?>
<?php if(file_exists($guide_path)) include $guide_path; ?>
<?php foreach($apiInfo as $info_key => $info_label): ?>
    <div class="form-group">
        <div class="col-md-2"><label class="pull-right"><?php echo $info_label; ?></label></div>
        <div class="col-md-10">
            <div class="col-lg-12"><input type="text" class="form-control" name="api[<?php echo $info_key; ?>]"/></div>
        </div>
        <div style="clear: both;"></div>
    </div>
<?php endforeach; ?>