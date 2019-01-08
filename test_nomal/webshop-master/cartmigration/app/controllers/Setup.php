<?php

class LECM_Controller_Setup
    extends LECM_Controller
{
    public function index()
    {
        $this->_render('setup.tpl');
    }

    public function install()
    {
        $dbConfig = Bootstrap::getDbConfig();
        if($dbConfig){
            $db = LECM_Db::getInstance($dbConfig);
            if($db){
                include_once _MODULE_APP_DIR_ . DS . 'setup' . DS . 'Install.php';
                $install = new LECM_Setup_Install();
                $install->run($db);
            }
        }
        $this->_redirect('index', 'setup');
        return;
    }

    public function uninstall()
    {
        $dbConfig = Bootstrap::getDbConfig();
        if($dbConfig){
            $db = LECM_Db::getInstance($dbConfig);
            if($db){
                include_once _MODULE_APP_DIR_ . DS . 'setup' . DS . 'Uninstall.php';
                $install = new LECM_Setup_Uninstall();
                $install->run($db);
            }
        }
        $this->_redirect('index', 'setup');
        return;
    }
}