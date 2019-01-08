<?php
    $breadCrumbs = $this->getBreadCrumbs();
    $flag = false;
?>
<?php if($breadCrumbs):?>
<div class="admin-panel-breadcrumbs col-md-6">
    <div class="admin-breadcrumb-title"><?php echo $this->getTitle();?></div>
    <div class="admin-breadcrumb-content">
        <ul class="breadcrumb">
        <?php foreach ($breadCrumbs as $breadCrumb):?>
            <li>
                <?php if(isset($breadCrumb['url'])):?>
                    <a href="<?php echo $breadCrumb['url']; ?>"><?php echo $breadCrumb['label']?></a>
                <?php else:?>

                <?php echo $breadCrumb['label']?>
                <?php endif;?>
            </li>
        <?php endforeach;?>
        </ul>
    </div>
</div>
<?php endif;?>