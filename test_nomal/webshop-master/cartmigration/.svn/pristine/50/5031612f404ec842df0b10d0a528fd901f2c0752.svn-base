<?php
$notice = $this->getGlobal('notice');
$limit = $notice['limit'];
if(is_string($limit)){
    $limit = ucfirst($limit);
}
?>
<div class="migration-padding">


<form action="" method="post" id="form-import">
    <input type="hidden" name="process" value="finish"/>
    <div class="panel">
        <div class="panel-body">
            <div class="import-warning">
                <!--                <img src="--><?php //echo Bootstrap::getUrl('pub/img/warning.png');?><!--" alt="">-->
                Migration is in progress! Please do not close your browser during the migration.
            </div>
            <div style="margin: 10px 0;">
                <p>Source cart: <strong><?php echo $notice['src']['cart_url']; ?></strong></p>
                <p>Target cart: <strong><?php echo $notice['target']['cart_url']; ?></strong></p>
                <p>Entity limit: <strong><?php echo $limit; ?></strong></p>
            </div>
            <?php if($notice['config']['clear_shop'] && $notice['target']['clear']['result'] != 'success'): ?>
                <div id="process-clear-data"><img src="<?php echo Bootstrap::getUrl('pub/img/loader-small.gif'); ?>"/> Clearing store ...</div>
            <?php endif; ?>

            <?php if($notice['config']['taxes']): ?>
                <div id="process-taxes" class="process-wrap">
                    <div class="process-name">Taxes</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['taxes']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['taxes']['imported'].'/'.$notice['process']['taxes']['total'].', Errors: '.$notice['process']['taxes']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-taxes">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['manufacturers']): ?>
                <div id="process-manufacturers" class="process-wrap">
                    <div class="process-name">Manufacturers</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['manufacturers']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['manufacturers']['imported'].'/'.$notice['process']['manufacturers']['total'].', Errors: '.$notice['process']['manufacturers']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-manufacturers">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['categories']): ?>
                <div id="process-categories" class="process-wrap">
                    <div class="process-name">Categories</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['categories']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['categories']['imported'].'/'.$notice['process']['categories']['total'].', Errors: '.$notice['process']['categories']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-categories">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['products']): ?>
                <div id="process-products" class="process-wrap">
                    <div class="process-name">Products</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['products']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['products']['imported'].'/'.$notice['process']['products']['total'].', Errors: '.$notice['process']['products']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-products">Retry</div>
                    </div>
                </div>

            <?php endif;?>
            <?php if($notice['config']['customers']): ?>
                <div id="process-customers" class="process-wrap">
                    <div class="process-name">Customers</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['customers']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['customers']['imported'].'/'.$notice['process']['customers']['total'].', Errors: '.$notice['process']['customers']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-customers">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['orders']): ?>
                <div id="process-orders" class="process-wrap">
                    <div class="process-name">Orders</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['orders']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['orders']['imported'].'/'.$notice['process']['orders']['total'].', Errors: '.$notice['process']['orders']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-orders">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['reviews']): ?>
                <div id="process-reviews" class="process-wrap">
                    <div class="process-name">Reviews</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['reviews']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['reviews']['imported'].'/'.$notice['process']['reviews']['total'].', Errors: '.$notice['process']['reviews']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-reviews">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['pages']): ?>
                <div id="process-pages" class="process-wrap">
                    <div class="process-name">Pages</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['pages']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['pages']['imported'].'/'.$notice['process']['pages']['total'].', Errors: '.$notice['process']['pages']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-pages">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['blocks']): ?>
                <div id="process-blocks" class="process-wrap">
                    <div class="process-name">Static blocks</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['blocks']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['blocks']['imported'].'/'.$notice['process']['blocks']['total'].', Errors: '.$notice['process']['blocks']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-blocks">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['widgets']): ?>
                <div id="process-widgets" class="process-wrap">
                    <div class="process-name">Widgets</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['widgets']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['widgets']['imported'].'/'.$notice['process']['widgets']['total'].', Errors: '.$notice['process']['widgets']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-widgets">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['polls']): ?>
                <div id="process-polls" class="process-wrap">
                    <div class="process-name">Polls</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['polls']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['polls']['imported'].'/'.$notice['process']['polls']['total'].', Errors: '.$notice['process']['polls']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-polls">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['transactions']): ?>
                <div id="process-transactions" class="process-wrap">
                    <div class="process-name">Transaction email</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['transactions']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['transactions']['imported'].'/'.$notice['process']['transactions']['total'].', Errors: '.$notice['process']['transactions']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-transactions">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['newsletters']): ?>
                <div id="process-newsletters" class="process-wrap">
                    <div class="process-name">Newsletter template</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['newsletters']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['newsletters']['imported'].'/'.$notice['process']['newsletters']['total'].', Errors: '.$notice['process']['newsletters']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-newsletters">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['users']): ?>
                <div id="process-users" class="process-wrap">
                    <div class="process-name">Users</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['users']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['users']['imported'].'/'.$notice['process']['users']['total'].', Errors: '.$notice['process']['users']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-users">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['rules']): ?>
                <div id="process-rules" class="process-wrap">
                    <div class="process-name">Rules</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['rules']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['rules']['imported'].'/'.$notice['process']['rules']['total'].', Errors: '.$notice['process']['rules']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-rules">Retry</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($notice['config']['cartrules']): ?>
                <div id="process-cartrules" class="process-wrap">
                    <div class="process-name">Cart Rules</div>
                    <div class="process-content">
                        <p class="process-bar">
                            <span class="process-bar-width" style="width: <?php echo $notice['process']['cartrules']['point'] ?>%;"></span>
                        </p>
                        <p class="console-log"><?php echo 'Imported: '.$notice['process']['cartrules']['imported'].'/'.$notice['process']['cartrules']['total'].', Errors: '.$notice['process']['cartrules']['error'] ?></p>
                    </div>
                    <div class="clear-both"></div>
                    <div class="try-import">
                        <div id="try-import-cartrules">Retry</div>
                    </div>
                </div>
            <?php endif; ?>


            <div class="console-wrap">
                <div class="console-title">Console</div>
                <div class="console-log" id="console-log-import">
                    <?php echo $notice['start_msg']; ?>
                </div>
            </div>
        </div>

        <div class="form-submit text-center">
            <div class="form-loading" id="form-import-loading"><img src="<?php echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?>"/> Processing ...</div>
            <div id="form-import-submit-wrap" class="display-none"><a href="javascript:void(0)" class="btn-submit" id="form-import-submit">Clear Cache & Reindex Data</a></div>
            <div id="retry-clear-shop-wrap" class="display-none"><a href="javascript:void(0)" class="btn-submit" id="retry-clear-shop">Retry</a></div>
        </div>
        <div class="clearfix"></div>
    </div>
</form>
</div>