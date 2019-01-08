<?php
$notice = $this->getGlobal('notice');
?>
<div class="migration-padding">


    <form action="" method="post" id="form-resume">
        <input type="hidden" name="process" value="resume"/>
        <div class="panel">
            <h3>Incompleted Last Import</h3>
            <div class="panel-body">
                <div class="form-group">
                    <div class="section-title">Source site url</div>
                    <div class="clearfix"></div>
                    <div>
                        <ul>
                            <li>
                                - <?php echo $notice['src']['cart_url'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    <div class="section-title">Target site url</div>
                    <div class="clearfix"></div>
                    <div>
                        <ul>
                            <li>
                                - <?php echo $notice['target']['cart_url'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php if ($notice['support']['site_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Shop</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['site'] as $src_site_value => $target_site_value): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['site'][$src_site_value]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['site'][$target_site_value]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['language_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Language</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['languages'] as $src_language_value => $target_language_value): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['languages'][$src_language_value]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['languages'][$target_language_value]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['category_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Root Category</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['categoryData'] as $src_category_value => $target_category_value): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['categoryData'][$src_category_value]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['categoryData'][$target_category_value]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['attribute_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Attribute</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['attributes'] as $src_attribute_value => $target_attribute_value): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['attributes'][$src_attribute_value]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['attributes'][$target_attribute_value]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['order_status_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Order Status</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['order_status'] as $src_order_status => $target_order_status): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['order_status'][$src_order_status]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['order_status'][$target_order_status]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['currency_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Currency</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['currencies'] as $src_currency => $target_currency): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['currencies'][$src_currency]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['currencies'][$target_currency]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['country_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Country</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['countries'] as $src_country => $target_country): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['countries'][$src_country]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['countries'][$target_country]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['support']['customer_group_map']): ?>
                    <div class="form-group">
                        <div class="form-title">Customer Group</div>
                        <div class="clearfix"></div>
                    </div>
                    <?php foreach ($notice['map']['customer_group'] as $src_customer_group => $target_customer_group): ?>
                        <div class="form-group">
                            <div class="col-md-4"><?php echo $notice['src']['customer_group'][$src_customer_group]; ?></div>
                            <div class="col-md-8">
                                <div class="col-md-6">
                                    <?php echo $notice['target']['customer_group'][$target_customer_group]; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="form-group">
                    <div class="form-title">Previous Process</div>
                    <div>
                        <ul>
                            <?php $entities = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages','blocks', 'widgets', 'polls', 'transactions', 'newsletters', 'users', 'rules', 'cartrules'); ?>
                            <?php foreach ($entities as $entity): ?>
                                <?php if ($notice['config'][$entity]): ?>
                                    <?php $entity_process = $notice['process'][$entity]; ?>
                                    <li>- <?php echo ucfirst($entity); ?>: <?php echo $entity_process['imported'] ?>
                                        /<?php echo $entity_process['total'] ?>
                                        completed, <?php echo $entity_process['error'] ?>
                                        errors <?php if ($entity_process['finish']) { ?><img
                                            src="<?php echo Bootstrap::getUrl('pub/img/success.png'); ?>"/><?php } ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <?php if ($this->isValueAvailable($notice['config'], array('add_new', 'clear_shop', 'img_des', 'pre_cus', 'pre_ord'))): ?>
                    <div class="form-group">
                        <div class="form-title">Additional Options</div>
                        <div>
                            <ul>
                                <?php if ($notice['config']['add_new']) { ?>
                                    <li>- Migrate recent data (adds new entities only)</li><?php } ?>
                                <?php if ($notice['config']['clear_shop']) { ?>
                                    <li>- Clear current data on Target Store before Migration</li><?php } ?>
                                <?php if ($notice['config']['img_des']) { ?>
                                    <li>- Transfer images from Product descriptions to Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_cus']) { ?>
                                    <li>- Preserve Customer IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_ord']) { ?>
                                    <li>- Preserve Order IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_prd']) { ?>
                                    <li>- Preserve Product IDs on Target Store (if Target Store is empty)</li><?php } ?>
                                <?php if ($notice['config']['seo']) { ?>
                                    <li>- Migrate categories and products SEO URLs</li><?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <div class="form-submit text-center">
                <div class="form-loading" id="form-resume-loading"><img
                            src="<?php echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?>"/> Processing ...
                </div>
                <div id="form-resume-submit-wrap"><a href="javascript:void(0)" class="btn-submit"
                                                     id="form-resume-submit">Resume</a></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </form>
</div>