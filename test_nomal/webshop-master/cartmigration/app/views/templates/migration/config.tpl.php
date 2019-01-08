<?php
$notice = $this->getGlobal('notice');
?>
<div class="migration-padding">

    <form action="" method="post" id="form-config">
        <input type="hidden" name="process" value="config"/>
        <div class="panel">
            <h3>Configuration</h3>
            <div class="panel-body">

                <div class="form-group" id="form-entities">
                    <div class="section-title">Entities to Migrate</div>
                    <div id="entity-section">
                        <ul>
                            <li>
                                <div><input type="checkbox" id="select-all-entities"/><label class="entity-label">Select
                                        All</label></div>
                            </li>
                            <?php if ($notice['support']['taxes']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="taxes"/><label class="entity-label">Taxes</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['manufacturers']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="manufacturers"/><label
                                                class="entity-label">Manufacturers</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['categories']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="categories"/><label
                                                class="entity-label">Categories</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['products']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="products"/><label
                                                class="entity-label">Products</label></div>
                                    <?php if ($notice['support']['reviews'] && !Bootstrap::getConfig('demo_mode')) { ?>
                                        <ul>
                                            <li>
                                                <div><input type="checkbox" class="lv2" name="reviews"/><label
                                                            class="entity-label">Reviews</label></div>
                                            </li>
                                        </ul>
                                    <?php } ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['customers']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="customers"/><label
                                                class="entity-label">Customers</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['orders']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="orders"/><label class="entity-label">Orders</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['pages']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="pages"/><label class="entity-label">Pages</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['blocks']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="blocks"/><label class="entity-label">Static
                                            blocks</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['widgets']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="widgets"/><label class="entity-label">Widgets</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['polls']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="polls"/><label class="entity-label">Polls</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['transactions']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="transactions"/><label
                                                class="entity-label">Transaction email</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['newsletters']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="newsletters"/><label
                                                class="entity-label">Newsletter template</label></div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['users']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="users"/><label class="entity-label">Users</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['rules']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="rules"/><label class="entity-label">Rules</label>
                                    </div>
                                </li><?php endif; ?>
                            <?php if ($notice['support']['cartrules']): ?>
                                <li class="lv0">
                                    <div><input type="checkbox" class="lv1" name="cartrules"/><label
                                                class="entity-label">Cart Rules</label></div>
                                </li><?php endif; ?>
                        </ul>
                        <label class="error display-none" id="error-entity-select">You must select at least one
                            entity!</label>
                    </div>
                </div>

                <div class="form-group" id="form-options">
                    <div class="section-title">Additional Options</div>
                    <div id="addition-section">
                        <ul>
                            <?php if ($notice['support']['add_new']): ?>
                                <li id="option_recent">
                                    <div><input type="checkbox" name="add_new"/><label class="entity-label">Migrate
                                            recent data (adds new entities only)</label></div>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['clear_shop']): ?>
                                <li id="option_clear">
                                    <div><input type="checkbox" name="clear_shop"/><label class="entity-label">Clear
                                            current data on Target Store before Migration</label></div>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['img_des'] && 1 == 0): ?>
                                <li>
                                    <div><input type="checkbox" name="img_des"/><label class="entity-label">Transfer
                                            images from Product descriptions to Target Store</label></div>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['pre_cus']): ?>
                                <li id="option_pre_cus">
                                    <div><input type="checkbox" name="pre_cus"/><label class="entity-label">Preserve
                                            Customer IDs on Target Store</label></div>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['pre_ord']): ?>
                                <li id="option_pre_order">
                                    <div><input type="checkbox" name="pre_ord"/><label class="entity-label">Preserve
                                            Order IDs on Target Store</label></div>
                                </li>
                            <?php endif; ?>
<!--                            --><?php //if ($notice['support']['pre_cat']): ?>
<!--                                <li id="option_pre_cat">-->
<!--                                    <div><input type="checkbox" name="pre_cat"/><label class="entity-label">Preserve-->
<!--                                            Category IDs on Target Store (if Target Store is empty)</label></div>-->
<!--                                </li>-->
<!--                            --><?php //endif; ?>
                            <?php if ($notice['support']['pre_prd']): ?>
                                <li id="option_pre_prd">
                                    <div><input type="checkbox" name="pre_prd"/><label class="entity-label">Preserve
                                            Product IDs on Target Store (if Target Store is empty)</label></div>
                                </li>
                            <?php endif; ?>
                            <?php if ($notice['support']['seo']): ?>
                                <li id="option_seo">
                                    <div><input type="checkbox" name="seo" id="choose-seo"/><label class="entity-label">Migrate
                                            categories and products SEO URLs</label></div>
                                </li>
                                <li>
                                    <div class="form-group" id="seo_plugin" style="display: none;">
                                        <div class="col-md-4">Choose Plugin Seo In Source Cart</div>
                                        <div class="col-md-8">
                                            <div class="col-md-6">
                                                <?php $seo = $this->getSeoPluginAvailable(); ?>
                                                <select class="form-control" name="seo_plugin">
                                                    <?php foreach ($seo as $seo_key => $seo_label): ?>
                                                        <option value="<?php echo $seo_key ?>"><?php echo $seo_label; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="clear-both"></div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php if ($notice['support']['site_map']): ?>
                    <div id="shop-section">
                        <div class="form-group">
                            <div class="section-title">Shop</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['site'] as $src_shop_value => $src_shop_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_shop_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control" name="site[<?php echo $src_shop_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['site'], $src_shop_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group display-none" id="error-site-duplicate">
                            <label class="error">Can not be the same Shop.</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['support']['language_map']): ?>
                    <div id="language-section">
                        <div class="form-group">
                            <div class="section-title">Language</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['languages'] as $src_language_value => $src_language_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_language_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="languages[<?php echo $src_language_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['languages'], $src_language_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group display-none" id="error-language-duplicate">
                            <label class="error">Can not be the same Language on target. You may have to create enough
                                target languages to map with your source languages.</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['support']['category_map']): ?>
                    <div id="category-section">
                        <div class="form-group">
                            <div class="section-title">Root Category</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['categoryData'] as $src_category_value => $src_category_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_category_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="categoryData[<?php echo $src_category_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['categoryData'], $src_category_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group display-none" id="error-category-root-duplicate">
                            <label class="error">Can not be the same Root category.</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['support']['attribute_map']): ?>
                    <div id="attribute-section">
                        <div class="form-group">
                            <div class="section-title">Attribute Set</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['attributes'] as $src_attribute_value => $src_attribute_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_attribute_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="attributes[<?php echo $src_attribute_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['attributes'], $src_attribute_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group display-none" id="error-attribute-duplicate">
                            <label class="error">Can not be the same Attribute.</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['support']['order_status_map']): ?>
                    <div id="order-status-section">
                        <div class="form-group">
                            <div class="section-title">Order Status</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['order_status'] as $src_order_status_value => $src_order_status_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_order_status_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="order_status[<?php echo $src_order_status_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['order_status'], $src_order_status_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <?php if ($notice['support']['currency_map']): ?>
                    <div id="currency-section">
                        <div class="form-group">
                            <div class="section-title">Currency</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['currencies'] as $src_currency_value => $src_currency_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_currency_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="currencies[<?php echo $src_currency_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['currencies'], $src_currency_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <?php if ($notice['support']['country_map']): ?>
                    <div id="country-section">
                        <div class="form-group">
                            <div class="section-title">Country</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['countries'] as $src_country_value => $src_country_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_country_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control" name="countries[<?php echo $src_country_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['countries'], $src_country_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <?php if ($notice['support']['customer_group_map']): ?>
                    <div id="customer-group-section">
                        <div class="form-group">
                            <div class="section-title">Customer Group</div>
                            <div class="clear-both"></div>
                        </div>
                        <?php foreach ($notice['src']['customer_group'] as $src_group_value => $src_group_label): ?>
                            <div class="form-group">
                                <div class="col-md-4"><?php echo $src_group_label; ?></div>
                                <div class="col-md-8">
                                    <div class="col-md-6">
                                        <select class="form-control"
                                                name="customer_group[<?php echo $src_group_value ?>]">
                                            <?php echo $this->toHtmlOption($notice['target']['customer_group'], $src_group_label); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear-both"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>


            </div>
            <div class="form-submit text-center">
                <div class="form-loading" id="form-config-loading"><img
                            src="<?php echo Bootstrap::getUrl('pub/img/loader-large.gif'); ?>"/> Processing ...
                </div>
                <div id="form-config-submit-wrap"><a href="javascript:void(0)" class="btn-submit"
                                                     id="form-config-submit">Next</a></div>
            </div>
            <!--        <div><a id="form-config-back" href="javascript:void(0)">&laquo; Back to previous Step</a></div>-->
            <div class="clear-both"></div>
        </div>
    </form>
</div>
