<script src="<?php echo Bootstrap::getUrl('pub/js/admin.js')?>"></script>

<div class="admin-panel-header row">
    <?php include Bootstrap::getTemplate('admin/breadcrumbs') ?>
    <div class="col-md-6 admin-header-action">
        <div class="admin__action">
            <i class=" fa fa-bell fa-2x inline-block admin__action-notify">
                <div class="notify-number display-none"></div>
            </i>
            <div class="inline-block " id="admin__action-parent-dropdown">
                <i class="fa fa-user fa-2x"></i>
                 <?php echo $this->getDataAccount('first_name') . ' ' . $this->getDataAccount('last_name'); ?>
                <i class="fa fa-caret-down fa-fw admin_action-dropdown"></i>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-admin-account">
                    <ul class="my_ul">
                        <a href="<?php echo Bootstrap::getUrlAdmin('user/info')?>">
                            <li>Account setting</li>
                        </a>
                        <a href="<?php echo Bootstrap::getUrlAdmin('user/login',array('action' => 'logout'))?>">
                            <li>Sign out</li>
                        </a>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>