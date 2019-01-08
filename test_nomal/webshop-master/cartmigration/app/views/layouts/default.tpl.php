<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" href="<?php echo Bootstrap::getUrl('favicon.ico'); ?>" type="image/x-icon" />
    <title>Cart Migration</title>
    <?php $cssFile = $this->getGlobal('cssFile'); ?>
    <?php foreach($cssFile as $css_file): ?>
        <link type="text/css" rel="stylesheet" media="screen" href="<?php echo Bootstrap::getUrl($css_file); ?>"/>
    <?php endforeach; ?>

    <?php $jsFile = $this->getGlobal('jsFile'); ?>
    <?php foreach($jsFile as $js_file): ?>
        <script type="text/javascript" src="<?php echo Bootstrap::getUrl($js_file); ?>"></script>
    <?php endforeach; ?>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
</head>

<body>
<!-- nav -->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="http://litextension.com/" target="_blank">
                <span class="logo"></span>
            </a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <?php if(!$this->_activeMenu('index', 'config')){?>
                    <li class="<?php if($this->_activeMenu('index')){?>active<?php }?>"><a href="<?php echo Bootstrap::getUrl(); ?>">Migration</a></li>
                <?php } ?>
                <?php if($this->_activeMenu('index', 'config')){?>
                    <li class="active"><a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setup', 'action' => 'index')); ?>">Setup</a></li>
                <?php }?>
                <?php if(!$this->_activeMenu('index', 'config') && !Bootstrap::getConfig('demo_mode')){?>
                    <li class="<?php if($this->_activeMenu('index', 'setting')){?>active<?php }?>"><a href="<?php echo Bootstrap::getUrl(null, array('controller' => 'setting', 'action' => 'index')); ?>">Settings</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php if(!(isset($_GET['controller']) && $_GET['controller'] == 'setting')): ?>

        <div class="progress_new">
            <div class="circle active" id="progress-1">
                <span class="label">1</span>
                <span class="title" id="circle-setup">Setup</span>
            </div>
            <span class="bar" id="bar-1"></span>
            <div class="circle " id="progress-2">
                <span class="label">2</span>
                <span class="title" id="circle-config">Configuration</span>
            </div>
            <span class="bar " id="bar-2"></span>
            <div class="circle" id="progress-3">
                <span class="label">3</span>
                <span class="title" id="circle-mig">Migration</span>
            </div>
        </div>
    <?php endif; ?>

</nav>

<!-- content -->
<div class="container">
    <?php $messages = LECM_Session::getKey('messages');?>
    <?php if($messages){ ?>
        <?php LECM_Session::unsetKey('messages'); ?>
        <div class="alert alert-info">
            <?php foreach($messages as $message){ ?>
                <p class="message-alert-<?php echo $message['type']?>"> - <?php echo $message['message']; ?></p>
            <?php } ?>
        </div>
    <?php } ?>

    <?php
    if($this->_content){
        $template_path = Bootstrap::getTemplate($this->_content);
        if($template_path){
            include $template_path;
        }
    }
    ?>
</div>

<!-- footer -->

</body>
</html>
