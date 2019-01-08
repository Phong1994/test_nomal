<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09/03/2018
 * Time: 10:07
 */
$role = $this->getRole();
?>
<div class="navigation-admin">
    <div class=" logo-admin">
        <a href="<?php echo Bootstrap::getUrlAdmin()?>">

            <img src="<?php echo Bootstrap::getImages('help/logo.png');?>" alt="">
        </a>
    </div>
    <ul id="menu-admin">

        <a href="<?php echo Bootstrap::getUrlAdmin(); ?>">
            <li id="section-dashboard" class="<?php if($this->_type == 'dashboard'){ echo 'menu-admin-active';}?>"><i class="fa fa-tachometer"></i>Dashboard</li>
        </a>
        <?php if ($role && $role['category']['view']): ?>
            <a href="<?php echo Bootstrap::getUrlAdmin('category'); ?>">
                <li id="section-category"><i class="fa fa-book"></i>Category</li>
            </a>
        <?php endif; ?>
        <?php if ($role && $role['product']['view']): ?>
            <a href="<?php echo Bootstrap::getUrlAdmin('product'); ?>">
                <li id="section-product"><i class="fa fa-product-hunt"></i>Product</li>
            </a>
        <?php endif; ?>
        <?php if ($role && $role['customer']['view']): ?>
            <a href="<?php echo Bootstrap::getUrlAdmin('customer'); ?>">
                <li id="section-customer"><i class="fa fa-users"></i>Customer</li>
            </a>
        <?php endif; ?>
        <?php if ($role && $role['order']['view']): ?>
            <a href="<?php echo Bootstrap::getUrlAdmin('order'); ?>">
                <li id="section-order"><i class="fa fa-shopping-cart"></i>Order</li>
            </a>
        <?php endif; ?>
        <a href="<?php echo Bootstrap::getUrlAdmin('setting'); ?>">
            <li id="section-setting"><i class="fa fa-cog"></i>Setting</li>
        </a>
    </ul>
</div>
