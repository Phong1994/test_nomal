<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['src']['cart_type'];
$guide_path = Bootstrap::getTemplate('migration/source/connector/' . $cart_type . '.tpl');
$guide_path_default = Bootstrap::getTemplate('migration/source/connector/default.tpl');
?>
<p class="title_support" id="text-support">Source Cart Token</p>
<?php
if(file_exists($guide_path)){
    include $guide_path;
}else{
    include $guide_path_default;
}
?>
