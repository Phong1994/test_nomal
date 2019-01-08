<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 11:50
 */

class Controller_Setup extends Controller
{
    const TITLE = 'setup';
    public function __construct()
    {
        $this->_title = self::TITLE;
    }

    protected $_install;
    protected $_notice;
    protected $_installAction = array(
        'core',
        'category',
        'product',
        'customer',
        'order',
        'extend',
    );
    protected $_nextInstall = array(
        'core' => 'category',
        'category' => 'product',
        'product' => 'customer',
        'customer' => 'order',
        'order' => 'extend',
        'extend' => false,
    );
    public function prepareProcess()
    {
        if(Bootstrap::isSetup()){
            $this->_redirect();
            return;
        }
        $this->addCss($this->getCssDefault())
             ->addJs($this->getJsDefault());
    }
    public function run(){
//        $this->deleteNotice();exit;
        if(empty($_POST))   {
            $this->_render('setup');
            return;
        }
        $function = $this->getParam('action');
        if($function && method_exists($this,$function)){
            $this->$function();
        }else{
            $this->responseError();
        }

    }
    protected function configDatabase(){
        $msg = '';
        $host = $this->getParam('host');
        $username = $this->getParam('username') ;
        $password = $this->getParam('password');
        $db_name = $this->getParam('database');
        $table_prefix = $this->getParam('prefix');
        $dbConfig = array(
            'db_host' => $host,
            'db_username' => $username,
            'db_password' => $password,
            'db_name' => $db_name,
            'db_prefix' => $table_prefix,
        );
        $db = Libs_Db::getInstance($dbConfig);
        if($db){
            $connect = $db->getConnect();
            if($connect){

                $file_config = _MODULE_APP_DIR_ . DS . 'etc/config.json';
                $string_config = json_encode($dbConfig);
                $success = false;
                try{
                    $success = file_put_contents($file_config, $string_config);
                } catch(Exception $e){
                    $msg = "Setup error: " . $e->getMessage();
                }
                if (!$success){
                    $this->responseError(null,$msg);
                }
                $template_path = Bootstrap::getTemplate('setup/install');
                ob_start();
                include $template_path;
                $html = ob_get_contents();
                ob_end_clean();
                $this->responseSuccess($html);
            }else{
                $msg = 'Not connect to database';
                $this->responseError(null,$msg);
            }
        }else{
            $msg = 'Not connect to database';
            $this->responseError(null,$msg);
        }
    }

    protected function installDatabase(){

        $notice = $this->getNotice();
        $class_install = $notice['class_install'];
        $base_model = Bootstrap::getBaseModel();
        if(version_compare($base_model->getVersionInstall($class_install),'1.0.0')<0){
            if($notice[$class_install]['status'] == 'process'){
                require_once _MODULE_APP_DIR_ . DS . 'setup' . DS . ucfirst($class_install).'.php';
                $class_name = 'Setup_Install_'.ucfirst($class_install);
                $install = new $class_name();
                $function_install = $notice[$class_install]['function'];
                $install_result = $install->install($function_install);
                if($install_result['result'] != 'success'){
                    $this->responseAjaxJson($install_result);
                }
                $msg = $install_result['msg'];
                $install_data = $install_result['data'];
                if($install_data['status'] == 'process'){
                    $function_install_next = $install_data['function'];
                    $notice[$class_install]['status'] = 'process';
                    $notice[$class_install]['function'] = $function_install_next;
                    $this->saveNotice($notice);
                    $this->responseAjaxJson(array(
                        'result' => 'process',
                        'msg' => $msg,
                    ));
                }else{
                    $base_model->setVersionInstall($class_install);
                }
                $next_install = $this->_nextInstall[$class_install];
                if(!$next_install){
                    $template_path = Bootstrap::getTemplate('setup/setup_web');
                    ob_start();
                    include $template_path;
                    $html = ob_get_contents();
                    ob_end_clean();
                    $this->responseSuccess($html);
                }
                $notice[$class_install]['status'] = 'success';
                $notice['class_install'] = $next_install;
                $this->saveNotice($notice);
                $this->responseAjaxJson(array(
                    'result' => 'process',
                    'msg' => $msg,
                ));
            }
        }else{

        }


        $next_install = $this->_nextInstall[$class_install];
        if(!$next_install){
            $template_path = Bootstrap::getTemplate('setup/setup_web');
            ob_start();
            include $template_path;
            $html = ob_get_contents();
            ob_end_clean();
            $this->responseSuccess($html);
        }
        $notice[$class_install]['status'] = 'success';
        $notice['class_install'] = $next_install;
        $this->saveNotice($notice);
        $this->responseAjaxJson(array(
            'result' => 'process',
            'msg' => '',
        ));
    }

    protected function createAdminAccount(){
        $admin_url = $this->getParam('admin_url');
        $admin_account = $this->getParam('admin_account');
        $admin_password = $this->getParam('admin_password');
        $admin_firstname = $this->getParam('admin_first_name');
        $admin_lastname = $this->getParam('admin_last_name');
        if($admin_url && $admin_account && $admin_password){
            $this->setConfig('admin_url',$admin_url);
            $model_account = Bootstrap::getModel('account');
            $data = array(
                'username' => $admin_account,
                'password' => $admin_password,
                'first_name' => $admin_firstname,
                'last_name' => $admin_lastname,
                'role_id' => 1,
            );
            $model_account->setData($data);
            try{
                $model_account->save();
                Bootstrap::setVersionInstall('1.0.0');
                $template_path = Bootstrap::getTemplate('setup/finish');
                ob_start();
                include $template_path;
                $html = ob_get_contents();
                ob_end_clean();
                $this->responseSuccess($html);
            }catch (Exception $e){
                $this->responseError('',$e->getMessage());
            }
        }else{
            $this->responseError('','Missing data');
        }
    }

    protected function getDefaultNotice(){
        return array(
            'class_install' => 'core',
            'core' => array(
                'status' => 'process',
                'function' => 'createSetupDatabaseTable',
            ),
            'category' => array(
                'status' => 'process',
                'function'=> 'createCategoryTable',
            ),
            'product' => array(
                'status' => 'process',
                'function'=> 'createAttributeTable',
            ),
            'customer' => array(
                'status' => 'process',
                'function'=> 'createCustomerTable',
            ),
            'order' => array(
                'status' => 'process',
                'function'=> 'createOrderTable',
            ),
            'extend' => array(
                'status' => 'success',
                'function' => '',
            ),

        );
    }
    protected function getNotice(){
        $notice = Session::getKey('notice');
        if($notice){
            return json_decode($notice,true);
        }
        return $this->getDefaultNotice();

    }
    protected function saveNotice($notice){
        Session::setKey('notice',json_encode($notice));
    }
    protected function deleteNotice(){
        Session::unsetKey('notice');
    }

}