<?php
$notice = $this->getGlobal('notice');
?>
<div class="migration-padding">


    <form action="" method="post" id="form-confirm">
        <input type="hidden" name="process" value="confirm"/>
        <div class="panel">
            <h3>Confirmation</h3>
            <div class="panel-body">

                <?php if ($notice['support']['site_map']): ?>
                    <div class="form-group">
                        <div class="section-title">Shop</div>
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
                        <div class="section-title">Language</div>
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
                        <div class="section-title">Root Category</div>
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
                        <div class="section-title">Attribute</div>
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
                        <div class="section-title">Order Status</div>
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
                        <div class="section-title">Currency</div>
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
                        <div class="section-title">Country</div>
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
                        <div class="section-title">Customer Group</div>
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
                    <div class="section-title">Entities to Migrate</div>
                    <div>
                        <ul>
                            <?php $entities = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages', 'blocks', 'widgets', 'polls', 'transactions', 'newsletters', 'users', 'rules', 'cartrules'); ?>
                            <?php foreach ($entities as $entity): ?>
                                <?php if ($notice['config'][$entity]): ?>
                                    <li class="form-group">- <?php echo ucfirst($entity); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <?php if ($this->isValueAvailable($notice['config'], array('add_new', 'clear_shop', 'img_des', 'pre_cus', 'pre_ord'))): ?>
                    <div class="form-group">
                        <div class="section-title">Additional Options</div>
                        <div>
                            <ul>
                                <?php if ($notice['config']['add_new']) { ?>

                                    <li class="form-group">- Migrate recent data (adds new entities only)</li><?php } ?>
                                <?php if ($notice['config']['clear_shop']) { ?>
                                    <li class="form-group">- Clear current data on Target Store before Migration</li><?php } ?>
                                <?php if ($notice['config']['img_des']) { ?>
                                    <li class="form-group">- Transfer images from Product descriptions to Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_cus']) { ?>
                                    <li class="form-group">- Preserve Customer IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_ord']) { ?>
                                    <li class="form-group">- Preserve Order IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_cat']) { ?>
                                    <li class="form-group">- Preserve Categories IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['pre_prd']) { ?>
                                    <li class="form-group">- Preserve Products IDs on Target Store</li><?php } ?>
                                <?php if ($notice['config']['seo']) { ?>
                                    <li class="form-group">- Migrate categories and products SEO URLs</li><?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <div class="section-title">Source site url</div>
                    <div class="clearfix"></div>
                    <div class="form-group">
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
                    <div class="form-group">
                        <ul>
                            <li>
                                - <?php echo $notice['target']['cart_url'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php if ($notice['config']['clear_shop']): ?>
                    <div class="form-group"
                         style="margin-bottom: 20px; padding: 10px; width: 100%; text-align: center;background: #FFD2A1; clear: both;">
                        <div>Warning: all current data of entities selected will be cleared.</div>
                    </div>
                <?php endif; ?>
                <?php if ($notice['config']['products'] && $notice['config']['pre_prd'] && !$notice['config']['real_pre_prd']): ?>
                    <div class="form-group"
                         style="margin-bottom: 20px; padding: 10px; width: 100%; text-align: center;background: #FFD2A1; clear: both;">

                        <div>Warning: Product in Target Store is not empty! You can't preserve Product IDs on Target
                            Store
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <div class="form-submit text-center">
                <div class="form-loading" id="form-confirm-loading"><img
                            src="<?php echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?>"/> Processing ...
                </div>
                <div id="form-confirm-submit-wrap"><a href="javascript:void(0)" class="btn-submit"
                                                      id="form-confirm-submit">Next</a>
                </div>
            </div>
            <div><a id="form-confirm-back" href="javascript:void(0)">&laquo; Back to previous Step</a></div>
            <div class="clearfix"></div>
        </div>
    </form>
</div>