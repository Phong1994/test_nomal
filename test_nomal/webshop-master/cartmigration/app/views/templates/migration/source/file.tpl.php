<?php
$notice = $this->getGlobal('notice');
$cart_type = $notice['src']['cart_type'];
if(!$cart_type){
    $cart_type = $this->getFirstSourceCartType();
}
$setupSourceCart = $this->setupSourceCart($cart_type);
$cart = Bootstrap::getModel($setupSourceCart['cart_model']);
$guide_path = Bootstrap::getTemplate('migration/source/file/' . $cart_type . '.tpl');
$fileInfo = $cart->getFileInfo();
?>
<div id="source-file-info">
<!--     <p><strong>Import Source Data</strong></p> -->
    <?php if(file_exists($guide_path)) include $guide_path; ?>
    <div class="">
        <div class="form-group">
            <div class="col-md-3"><strong>Resource</strong></div>
            <div class="col-md-3"><strong>Upload</strong></div>
            <div class="col-md-6"><strong>Upload result</strong></div>
            <div class="clear-both"></div>
        </div>
        <?php $i = 0; ?>
        <?php foreach($fileInfo as $info_key => $info_label): ?>
            <div class="form-group <?php if($i%2){ ?>upload-row-even<?php } else { ?>upload-row-odd<?php } ?>">
                <div class="col-md-3"><?php echo $info_label; ?></div>
                <div class="col-md-3" style="overflow: hidden;"><input type="file" name="<?php echo $info_key; ?>" id="input-file-<?php echo $info_key; ?>"/></div>
                <div class="col-md-6 result-upload" id="result-upload-<?php echo $info_key?>"></div>
                <div class="clear-both"></div>
            </div>
            <?php $i++; ?>
            <?php $file_guide_path = Bootstrap::getTemplate('migration/source/file/' . $cart_type . '/' . $info_key . '.tpl'); ?>

            <?php if(file_exists($file_guide_path)){ ?>
                <div class="form-group <?php if($i%2){ ?>upload-row-even<?php } else { ?>upload-row-odd<?php } ?>">
                    <?php include $file_guide_path; ?>
                </div>
                <?php $i++; ?>
            <?php } ?>
        <?php endforeach; ?>
        <div class="form-group">
            <div class="clear-both display-none" id="form-upload-loading"><img src="<?php echo Bootstrap::getUrl('pub/img/loader-small.gif'); ?>"/> Uploading ...</div>
        </div>
        <div class="form-group" id="form-upload-submit-wrap">
            <div class="col-md-3"></div>
            <div class="col-md-9"><a href="javascript:void(0)" class="btn-submit" id="form-upload-submit">Upload</a></div>
            <div class="clear-both"></div>
        </div>
    </div>
</div>