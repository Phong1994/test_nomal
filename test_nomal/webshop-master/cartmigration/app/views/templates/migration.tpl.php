<?php
$notice = $this->getGlobal('notice');
$type           = Bootstrap::getModel('type');
$sourceCartType = $type->sourceCarts();
$src_cart_type  = $notice['src']['cart_type'];
if (!$src_cart_type) {
    $src_cart_type = $this->getFirstSourceCartType();
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $.CartMigration({
            url: '<?php echo Bootstrap::getUrl(null, array('controller' => 'import', 'action' => 'index'))?>',
            resume_process: '<?php echo $notice['resume']['process'];?>',
            resume_type: '<?php echo $notice['resume']['type'];?>'
        });
    });
</script>

<div class="row" id="main">

    <div id="migration" class="col-md-9">
<!--        <div id="menu-content" class="clear-both">-->
<!--            <div id="menu-setup" class="menu-step menu-active"><strong>Setup</strong></div>-->
<!--            <div id="menu-config" class="menu-step"><strong>Configuration</strong></div>-->
<!--            <div id="menu-import" class="menu-step"><strong>Migration</strong></div>-->

<!--        </div>-->

        <div id="migration-content" class="clear-both">
            <!--    <div id="recent-content">--><?php //include Bootstrap::getTemplate('migration/recent.tpl'); ?><!--</div>-->

            <?php if($notice['running']): ?>
                <div id="resume-content"><?php include Bootstrap::getTemplate('migration/resume.tpl'); ?></div>
            <?php endif; ?>


            <div id="setup-content" ><?php include Bootstrap::getTemplate('migration/setup.tpl'); ?></div>

            <div id="source-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/source.tpl'); ?></div>

            <div id="target-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/target.tpl'); ?></div>

            <div id="storage-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/storage.tpl'); ?></div>

            <div id="config-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/config.tpl'); ?></div>

            <div id="confirm-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/confirm.tpl'); ?></div>

            <div id="import-content" class="display-none"><?php //include Bootstrap::getTemplate('migration/import.tpl'); ?></div>
        </div>

        <div id="footer-content" style="padding: 20px;">
            <p class="text-center">Cart Migration version <?php echo Bootstrap::getVersionInstall(); ?> by LitExtension</p>
        </div>

    </div>
    <div class="col-md-3 " id="parent_support">
        <div id="support" class="">

            <div id="support_content">
                <div id="support_content_2" class="display-block">
                    <?php include Bootstrap::getTemplate('migration/source/support/type.tpl');?>
                </div>

                <div id="support_content_3" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/source/support/url.tpl');?>

                </div>
                <div id="support_content_4" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/source/support/token.tpl');?>

                </div>
                <div id="support_content_5" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/target/support/type.tpl');?>

                </div>
                <div id="support_content_6" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/target/support/url.tpl');?>

                </div>
                <div id="support_content_7" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/target/support/token.tpl');?>

                </div>
                <div id="support_content_8" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/entities.tpl');?>

                </div>
                <div id="support_content_9" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/recent.tpl');?>

                </div>
                <div id="support_content_10" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/clear.tpl');?>

                </div>
                <div id="support_content_11" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/preCus.tpl');?>

                </div>
                <div id="support_content_12" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/preOrder.tpl');?>

                </div>
                <div id="support_content_13" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/prePrd.tpl');?>

                </div>
                <div id="support_content_14" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/additional/seo.tpl');?>

                </div>
                <div id="support_content_15" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/language.tpl');?>

                </div>
                <div id="support_content_16" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/category.tpl');?>

                </div>
                <div id="support_content_17" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/attribute.tpl');?>

                </div>
                <div id="support_content_18" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/order.tpl');?>

                </div>
                <div id="support_content_19" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/currency.tpl');?>

                </div>
                <div id="support_content_20" class="display-none">
                    <?php include Bootstrap::getTemplate('migration/support/map/group.tpl');?>

                </div>
            </div>
            <div id="footer_support">
                <ul class="pagination" id="pagination">
                    <li id="pre-support"><a href="javascript:void(0)"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></li>
                    <li id="next-support"><a href="javascript:void(0)"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
