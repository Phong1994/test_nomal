<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['target']['cart_type'];
$guide_path = Bootstrap::getTemplate('migration/target/connector/' . $cart_type . '.tpl');
$guide_path_default = Bootstrap::getTemplate('migration/target/connector/default.tpl');
?>
<p class="title_support" id="text-support">Target Cart Token</p>
<?php
if(file_exists($guide_path)){
    include $guide_path;
}else{
    include $guide_path_default;
}
?>
