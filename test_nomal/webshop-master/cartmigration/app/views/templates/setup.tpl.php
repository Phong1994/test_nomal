<?php
$install_label = '';
if(!Bootstrap::isSetup()){
    $install_label = 'Install';
} else {
    $install_label = 'Upgrade';
}
?>

<div class="page-header clearfix">
    <h1 class="pull-left">Setup</h1>
    <div class="pull-right" style="margin-top: 10px;padding-right: 30px;">
        <a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'config'))?>" style="font-size: 3em;" title="Configurable"><i class="fa fa-cogs fa-th-large"></i></a>
    </div>
</div>
<div class="col-lg-6" style="margin: 50px 25%;">
    <table class="table">
        <tr>
            <th class="col-md-6">Install Version</th>
            <td class="col-md-6"><?php if(Bootstrap::isSetup()){echo Bootstrap::getVersionInstall();} else { echo "Not install";}?></td>
        </tr>
        <?php if(Bootstrap::isUpgrade()){ ?>
            <tr>
                <th>New Version</th>
                <td><?php echo Bootstrap::getConfig('version')?></td>
            </tr>
        <?php } ?>
        <tr>
            <?php if(!Bootstrap::isSetup()){ ?>
                <td colspan="0">
                    <a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setup', 'action' => 'install'))?>" class="btn btn-primary col-md-4" style="margin-left: 30%;"><?php echo $install_label; ?></a>
                </td>
            <?php } else { ?>
                <?php if(Bootstrap::isUpgrade()){ ?>
                    <td>
                        <a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setup', 'action' => 'install'))?>" class="btn btn-primary col-md-6" style="margin-left: 25%;"><?php echo $install_label; ?></a>
                    </td>
                    <td>
                        <a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setup', 'action' => 'uninstall'))?>" class="btn btn-default col-md-6" style="margin-left: 25%;">Uninstall</a>
                    </td>
                <?php } else { ?>
                    <td colspan="0">
                        <a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setup', 'action' => 'uninstall'))?>" class="btn btn-default col-md-4" style="margin-left: 30%;">Uninstall</a>
                    </td>
                <?php } ?>
            <?php } ?>
        </tr>
    </table>
</div>