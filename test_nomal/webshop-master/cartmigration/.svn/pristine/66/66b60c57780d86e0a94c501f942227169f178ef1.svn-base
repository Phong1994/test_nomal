<?php

class LECM_Controller_Config
    extends LECM_Controller
{

    public function prepareProcess()
    {
        $this->addCss($this->getCssDefault())
            ->addJs($this->getJsDefault());
    }

    public function index()
    {
        $dbConfig = array(
            'db_host' => '',
            'db_username' => '',
            'db_password' => '',
            'db_name' => '',
            'db_prefix' => '',
            'db_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'litdb'
        );
        $db = LECM_Db::getInstance($dbConfig);
        if ($db) {
            $connect = $db->getConnect();
            if ($connect) {
                $file_sample = _MODULE_APP_DIR_ . DS . 'setup' . DS . 'config.sample.php';
                $file_config = _MODULE_APP_DIR_ . DS . 'etc' . DS . 'config.inc.php';
                @unlink($file_config);
                $config = array(
                    'database_host' => $dbConfig['db_host'],
                    'database_username' => $dbConfig['db_username'],
                    'database_password' => $dbConfig['db_password'],
                    'database_dbname' => $dbConfig['db_name'],
                    'database_tableprefix' => $dbConfig['db_prefix'],
                    'database_dbpath' => $dbConfig['db_path']
                );
                $content_sample = file_get_contents($file_sample);
                foreach ($config as $key => $value) {
                    $key = trim($key);
                    $value = trim($value);
                    $content_sample = str_replace($key, $value, $content_sample);
                }
                $success = false;
                try {
                    $success = file_put_contents($file_config, $content_sample);
                } catch (Exception $e) {
                    $this->_setMessage("Setup error " . $e->getMessage(), 'error');
                }

                include_once _MODULE_APP_DIR_ . DS . 'setup' . DS . 'Uninstall.php';
                include_once _MODULE_APP_DIR_ . DS . 'setup' . DS . 'Install.php';
                $uninstall = new LECM_Setup_Uninstall();
                $uninstall->run($db);
                $install = new LECM_Setup_Install();
                $install->run($db);

                if ($success) {
                    $this->_redirect('index');
                }
            } else {
                $this->_setMessage('Could not connect to database.', 'error');
            }
        } else {
            $this->_setMessage('Could not connect to database.', 'error');
        }
        $this->_render('config.tpl');
        exit();
    }

}