<?php

class LECM_Controller_Import
    extends LECM_Controller
{
    protected $_importAction = array(
        'taxes',
        'manufacturers',
        'categories',
        'products',
        'customers',
        'orders',
        'reviews',
        'pages',
        'blocks',
        'widgets',
        'polls',
        'transactions',
        'newsletters',
        'users',
        'rules',
        'cartrules'

    );
    protected $_nextAction = array(
        'taxes'         => 'manufacturers',
        'manufacturers' => 'categories',
        'categories'    => 'products',
        'products'      => 'customers',
        'customers'     => 'orders',
        'orders'        => 'reviews',
        'reviews'       => 'pages',
        'pages' => 'blocks',
        'blocks' => 'widgets',
        'widgets' => 'polls',
        'polls' => 'transactions',
        'transactions' => 'newsletters',
        'newsletters' => 'users',
        'users' => 'rules',
        'rules' => 'cartrules',
        'cartrules' => false,
    );
    protected $_simpleAction = array(
        'taxes'         => 'tax',
        'manufacturers' => 'manufacturer',
        'categories'    => 'category',
        'products'      => 'product',
        'customers'     => 'customer',
        'orders'        => 'order',
        'reviews'       => 'review',
        'pages'         => 'page',
        'blocks' => 'block',
        'widgets' => 'widget',
        'polls' => 'poll',
        'transactions' => 'transaction',
        'newsletters' => 'newsletter',
        'users' => 'user',
        'rules' => 'rule',
        'cartrules' => 'cartrule',
    );

    /**
     * TODO: INIT
     */
    public function prepareProcess()
    {
        return $this;
    }

    public function index()
    {
        $process = isset($_POST['process']) ? $_POST['process'] : false;
        if ($process) {
            if (method_exists($this, $process)) {
                $this->$process();

                return;
            } else {
                $this->_redirect('index', 'index');

                return;
            }
        } else {
            $this->_redirect('index', 'index');

            return;
        }
    }

    /**
     * TODO: GUI
     */

    protected function recentData()
    {

    }


    protected function resume()
    {
        $response = $this->defaultResponse();
        $cart     = Bootstrap::getModel('cart');
        $this->_initNotice($cart);

        $cart->setNotice($this->_notice);
        $prepareDisplayResume = $cart->prepareDisplayResume();
        if ($prepareDisplayResume['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplayResume);

            return;
        }
        $this->_notice = $cart->getNotice();

        $sourceCart          = $this->getSourceCart($cart);
        $displayResumeSource = $sourceCart->displayResumeSource();
        if ($displayResumeSource['result'] != 'success') {
            $this->responseAjaxJson($displayResumeSource);

            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $targetCart          = $this->getTargetCart($cart);
        $displayResumeTarget = $targetCart->displayResumeTarget();
        if ($displayResumeTarget['result'] != 'success') {
            $this->responseAjaxJson($displayResumeTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $cart->setNotice($this->_notice);
        $displayResume = $cart->displayResume();
        if ($displayResume['result'] != 'success') {
            $this->responseAjaxJson($displayResume);

            return;
        }
        $this->_notice = $cart->getNotice();

        $this->_notice['start_msg'] = $cart->consoleSuccess('Resuming ...');

        $template_path = Bootstrap::getTemplate('migration/import.tpl');
        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        $save_notice = $this->saveNotice($cart);
        if (!$save_notice) {
            $this->responseAjaxJson($cart->errorDatabase());

            return;
        }

        $response['result'] = 'success';
        $response['html']   = $html;
        $this->responseAjaxJson($response);

        return;
    }

    protected function changeSource()
    {
        $this->_initNotice();
        $cart_type                         = $_POST['source_cart_type'];
        $this->_notice['src']['cart_type'] = $cart_type;
        $setupSourceCart                   = $this->setupSourceCart($cart_type);
        $view_path                         = $setupSourceCart['view_path'];
        $show_next                         = ($setupSourceCart['setup_type'] == 'file') ? 'false' : 'true';
        $support                         = $setupSourceCart['support_token'];
        ob_start();
        include $support;
        $support_html = ob_get_contents();
        ob_end_clean();
        ob_start();
        include $view_path;
        $html = ob_get_contents();
        ob_end_clean();
        $response = array(
            'result'    => 'show',
            'html'      => $html,
            'support'   => $support_html,
            'show_next' => $show_next,
        );
        $this->responseAjaxJson($response);
    }

    protected function changeTarget()
    {
//        var_dump($_POST);exit;
        $this->_initNotice();
        $cart_type                            = $_POST['target_cart_type'];
        $this->_notice['target']['cart_type'] = $cart_type;
        $setupTargetCart                      = $this->setupTargetCart($cart_type);
        $view_path                            = $setupTargetCart['view_path'];
        $support                         = $setupTargetCart['support_token'];
        ob_start();
        include $support;
        $support_html = ob_get_contents();
        ob_end_clean();
        ob_start();
        include $view_path;
        $html = ob_get_contents();
        ob_end_clean();
        $response = array(
            'result' => 'show',
            'html'   => $html,
            'support'=> $support_html,

        );
        $this->responseAjaxJson($response);
    }

    protected function upload()
    {
        $response        = $this->defaultResponse();
        $router          = Bootstrap::getModel('cart');
        $previous_notice = $this->getNotice($router);
        if ($previous_notice['src']['config']['folder']) {
            $previous_folder = _MODULE_DIR_ . DS . 'media' . DS . $previous_notice['src']['config']['folder'];
            $router->deleteDir($previous_folder);
            if ($previous_notice['src']['cart_type']) {
                $previous_cart_name = $router->getCart($previous_notice['src']['cart_type'], $previous_notice['src']['config']['version']);
                $previous_cart      = Bootstrap::getModel($previous_cart_name);
                if ($previous_cart) {
                    $previous_cart->setType('src')->setNotice($previous_notice);
                    $previous_cart->clearPreviousSection();
                }
            }
        }
        $delete_notice = $this->deleteNotice($router);
        if (!$delete_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->_initNotice($router);
        $cart_type                                = $this->getParam('cart_type', '');
        $cart_url                                 = $this->getParam('cart_url', '');
        $cart_url                                 = $router->formatUrl($cart_url);
        $this->_notice['src']['cart_type']        = $cart_type;
        $this->_notice['src']['cart_url']         = $cart_url;
        $this->_notice['src']['config']['folder'] = $router->createFolderUpload($cart_url);

        $prepareSourceCart         = $this->getSourceCart($router);
        $prepareDisplaySetupSource = $prepareSourceCart->prepareDisplaySetupSource();
        if ($prepareDisplaySetupSource['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplaySetupSource);

            return;
        }
        $this->_notice = $prepareSourceCart->getNotice();

        $sourceCart    = $this->getSourceCart($router);
        $prepareUpload = $sourceCart->prepareUpload();
        if ($prepareUpload['result'] != 'success') {
            $this->responseAjaxJson($prepareUpload);

            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $upload_desc = _MODULE_DIR_ . DS . 'media' . DS . $this->_notice['src']['config']['folder'];
        @mkdir($upload_desc);

        $fileInfo   = $sourceCart->getFileInfo();
        $upload_msg = array();
        $libFile    = new LECM_File();
        $allowExt   = $sourceCart->getAllowExtensions();
        foreach ($fileInfo as $info_key => $info_label) {
            if (isset($_FILES[$info_key])) {
                $upload_name = $sourceCart->getUploadFileName($info_key);
                $upload      = $libFile->upload($_FILES[$info_key], $upload_desc, $upload_name, $allowExt);
                if ($upload) {
                    $this->_notice['src']['config']['file'][$info_key] = true;
                    $upload_msg[$info_key]                             = array(
                        'elm' => '#result-upload-' . $info_key,
                        'msg' => "<div class='uir-success'> Uploaded successfully.</div>"
                    );
                } else {
                    $this->_notice['src']['config']['file'][$info_key] = true;
                    $upload_msg[$info_key]                             = array(
                        'elm' => '#result-upload-' . $info_key,
                        'msg' => "<div class='uir-warning'> Upload failed.</div>"
                    );
                }
            } else {
                $this->_notice['src']['config']['file'][$info_key] = false;
            }
        }

        $sourceCart->setNotice($this->_notice);
        $displayUpload = $sourceCart->displayUpload($upload_msg);
        if ($displayUpload['result'] != 'success') {
            $this->responseAjaxJson($displayUpload);

            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $response['result'] = 'success';
        $response['msg']    = $displayUpload['msg'];

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->responseAjaxJson($response);

        return;
    }


    protected function setupCart(){
        $setupSource = $this->setupSource();
        if($setupSource['result'] != 'success'){
            $this->responseAjaxJson($setupSource);
            return;
        }
        $this->responseAjaxJson($this->setupTarget());
    }

    /**
     *
     */
    protected function setupSource()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');

        $cart_type    = $this->getParam('source_cart_type', '');
        $isInitNotice = $router->isInitNotice($cart_type);

        if (!$isInitNotice) {
            $delete_notice = $this->deleteNotice($router);
            if (!$delete_notice) {
//                $this->responseAjaxJson($router->errorDatabase());
//                var_dump(1);exit;
                return $router->errorDatabase();
            }
//            $cart_url = $this->getParam('cart_url');
            $cart_url = $this->getParam('source_cart_url');
            $cart_url = $router->formatUrl($cart_url);
            $this->_initNotice($router);
            $this->_notice['src']['cart_type'] = $cart_type;
            $this->_notice['src']['cart_url']  = $cart_url;
            $prepareSourceCart                 = $this->getSourceCart($router);
            $prepareDisplaySetupSource         = $prepareSourceCart->prepareDisplaySetupSource();
            if ($prepareDisplaySetupSource['result'] != 'success') {
//                $this->responseAjaxJson($prepareDisplaySetupSource);
//                var_dump(2);exit;
                return $prepareDisplaySetupSource;
            }
            $this->_notice = $prepareSourceCart->getNotice();
        } else {
            $this->_initNotice($router);
        }
        $sourceCart         = $this->getSourceCart($router);
        $displaySetupSource = $sourceCart->displaySetupSource();
        if ($displaySetupSource['result'] != 'success') {
//            $this->responseAjaxJson($displaySetupSource);
//            var_dump(3);exit;
            return $displaySetupSource;
        }
        $this->_notice = $sourceCart->getNotice();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
//            $this->responseAjaxJson($router->errorDatabase());
//            var_dump(4);exit;
            return $router->errorDatabase();
        }

        $response['result'] = 'success';
//        $response['html']   = $html;
//        $this->responseAjaxJson($response);

        return $response;
    }



    protected function storageOrSetup()
    {
        $router = Bootstrap::getModel('cart');
        $this->_initNotice($router);
        $current        = $this->getParam('current', '');
        $src_cart_type  = $this->_notice['src']['cart_type'];
        $src_setup_type = $router->sourceCartSetup($src_cart_type);
        if ($src_setup_type == 'file' && $current != 'storage') {
            $this->storage();
        } else {
            $this->setupTarget();
        }

        return;
    }

    protected function storage()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');

        $cart_type                            = $this->getParam('cart_type', '');
        $cart_url                             = $this->getParam('cart_url', '');
        $cart_url                             = $router->formatUrl($cart_url);
        $this->_notice['target']['cart_type'] = $cart_type;
        $this->_notice['target']['cart_url']  = $cart_url;
        $prepareTargetCart                    = $this->getTargetCart($router);
        $prepareDisplaySetupTarget            = $prepareTargetCart->prepareDisplaySetupTarget();
        if ($prepareDisplaySetupTarget['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplaySetupTarget);

            return;
        }
        $this->_notice = $prepareTargetCart->getNotice();

        $targetCart         = $this->getTargetCart($router);
        $displaySetupTarget = $targetCart->displaySetupTarget();
        if ($displaySetupTarget['result'] != 'success') {
            $this->responseAjaxJson($displaySetupTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $prepareDisplayStorage = $router->prepareDisplayStorage();
        if ($prepareDisplayStorage['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplayStorage);

            return;
        }
        $this->_notice = $router->getNotice();

        $sourceCart           = $this->getSourceCart($router);
        $displayStorageSource = $sourceCart->displayStorageSource();
        if ($displayStorageSource['result'] != 'success') {
            $this->responseAjaxJson($displayStorageSource);

            return;
        }

        $targetCart->setNotice($this->_notice);
        $displayStorageTarget = $targetCart->displayStorageTarget();
        if ($displayStorageTarget['result'] != 'success') {
            $this->responseAjaxJson($displaySetupTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $displayStorage = $router->displayStorage();
        if ($displayStorage['result'] != 'success') {
            $this->responseAjaxJson($displayStorage);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $template_path = Bootstrap::getTemplate('migration/storage.tpl');
        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $response['result']  = 'success';
        $response['html']    = $html;
        $response['elm']     = '#storage-content';
        $response['storage'] = 'yes';
        $this->responseAjaxJson($response);

        return;
    }

    protected function storageData()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');
        $this->_initNotice($router);
        $sourceCart    = $this->getSourceCart($router);
        $storageData   = $sourceCart->storageData();
        $this->_notice = $sourceCart->getNotice();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->responseAjaxJson($storageData);

        return;
    }

    protected function setupTarget()
    {
        $response       = $this->defaultResponse();
        $router         = Bootstrap::getModel('cart');
        $src_cart_type  = $this->_notice['src']['cart_type'];
        $src_setup_type = $router->sourceCartSetup($src_cart_type);
        if ($src_setup_type != 'file') {
            $cart_type                            = $this->getParam('target_cart_type', '');
            $cart_url                             = $this->getParam('target_cart_url', '');
            $cart_url                             = $router->formatUrl($cart_url);
            $this->_notice['target']['cart_type'] = $cart_type;
            $this->_notice['target']['cart_url']  = $cart_url;
            $prepareTargetCart                    = $this->getTargetCart($router);
            $prepareDisplaySetupTarget            = $prepareTargetCart->prepareDisplaySetupTarget();
            if ($prepareDisplaySetupTarget['result'] != 'success') {
//                $this->responseAjaxJson($prepareDisplaySetupTarget);
//                var_dump(1);exit;
                return $prepareDisplaySetupTarget;
            }
            $this->_notice      = $prepareTargetCart->getNotice();
            $targetCart         = $this->getTargetCart($router);
            $displaySetupTarget = $targetCart->displaySetupTarget();
            if ($displaySetupTarget['result'] != 'success') {
//                $this->responseAjaxJson($displaySetupTarget);
//                var_dump(2);exit;

                return $displaySetupTarget;
            }
            $this->_notice = $targetCart->getNotice();
        }
        $router->setNotice($this->_notice);
        $prepareDisplayConfig = $router->prepareDisplayConfig();
        if ($prepareDisplayConfig['result'] != 'success') {
//            $this->responseAjaxJson($prepareDisplayConfig);
//            var_dump(3);exit;

            return $prepareDisplayConfig;
        }
        $this->_notice = $router->getNotice();
        $sourceCart          = $this->getSourceCart($router);
        $displayConfigSource = $sourceCart->displayConfigSource();
        if ($displayConfigSource['result'] != 'success') {
//            $this->responseAjaxJson($displayConfigSource);
//            var_dump(4);exit;

            return $displayConfigSource;
        }
        $this->_notice = $sourceCart->getNotice();




        $targetCart          = $this->getTargetCart($router);
        $displayConfigTarget = $targetCart->displayConfigTarget();
        if ($displayConfigTarget['result'] != 'success') {
//            $this->responseAjaxJson($displayConfigTarget);
//            var_dump(5);exit;

            return $displayConfigTarget;
        }
        $this->_notice = $targetCart->getNotice();

        // var_dump($this->_notice);exit;
        $router->setNotice($this->_notice);
        $displayConfig = $router->displayConfig();
        if ($displayConfig['result'] != 'success') {
//            $this->responseAjaxJson($displayConfig);
//            var_dump(6);exit;

            return $displayConfig;
        }
        $this->_notice = $router->getNotice();

        $template_path = Bootstrap::getTemplate('migration/config.tpl');
        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {

//            $this->responseAjaxJson($router->errorDatabase());
//            var_dump(7);exit;

            return $router->errorDatabase();
        }

        $response['result'] = 'success';
        $response['html']   = $html;
        $response['elm']    = '#config-content';
//        $this->responseAjaxJson($response);
        return $response;
    }

    protected function config()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');
        $this->_initNotice($router);
        $router->setNotice($this->_notice);
        $prepareDisplayConfirm = $router->prepareDisplayConfirm();
        if ($prepareDisplayConfirm['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplayConfirm);

            return;
        }
        $this->_notice = $router->getNotice();

        $sourceCart           = $this->getSourceCart($router);
        $displayConfirmSource = $sourceCart->displayConfirmSource();
        if ($displayConfirmSource['result'] != 'success') {
            $this->responseAjaxJson($displayConfirmSource);

            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $targetCart           = $this->getTargetCart($router);
        $displayConfirmTarget = $targetCart->displayConfirmTarget();
        if ($displayConfirmTarget['result'] != 'success') {
            $this->responseAjaxJson($displayConfirmTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $displayConfirm = $router->displayConfirm();
        if ($displayConfirm['result'] != 'success') {
            $this->responseAjaxJson($displayConfirm);

            return;
        }
        $this->_notice = $router->getNotice();

        $template_path = Bootstrap::getTemplate('migration/confirm.tpl');
        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $response['result'] = 'success';
        $response['html']   = $html;
        $this->responseAjaxJson($response);

        return;
    }

    protected function confirm()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');
        $this->_initNotice($router);

        $router->setNotice($this->_notice);
        $prepareDisplayImport = $router->prepareDisplayImport();
        if ($prepareDisplayImport['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplayImport);

            return;
        }
        $this->_notice = $router->getNotice();

        $sourceCart          = $this->getSourceCart($router);
        $displayImportSource = $sourceCart->displayImportSource();
        if ($displayImportSource['result'] != 'success') {
            $this->responseAjaxJson($displayImportSource);

            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $targetCart          = $this->getTargetCart($router);
        $displayImportTarget = $targetCart->displayImportTarget();
        if ($displayImportTarget['result'] != 'success') {
            $this->responseAjaxJson($displayImportTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $displayImport = $router->displayImport();
        if ($displayImport['result'] != 'success') {
            $this->responseAjaxJson($displayImport);

            return;
        }
        $this->_notice = $router->getNotice();

        if ($this->_notice['config']['clear_shop']) {
            $this->_notice['start_msg'] = $router->consoleSuccess('Clearing store ...');
        } else {
            $this->_notice['start_msg'] = $router->getMsgStartImport('taxes');
        }

        $template_path = Bootstrap::getTemplate('migration/import.tpl');
        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $response['result'] = 'success';
        $response['html']   = $html;
        $this->responseAjaxJson($response);

        return;
    }

    protected function finish()
    {
        $router = Bootstrap::getModel('cart');
        $this->_initNotice($router);

        $router->setNotice($this->_notice);
        $prepareDisplayFinish = $router->prepareDisplayFinish();
        if ($prepareDisplayFinish['result'] != 'success') {
            $this->responseAjaxJson($prepareDisplayFinish);

            return;
        }
        $this->_notice = $router->getNotice();

        $sourceCart          = $this->getSourceCart($router);
        $displayFinishSource = $sourceCart->displayFinishSource();
        if ($displayFinishSource['result'] != 'success') {
            $this->responseAjaxJson($displayFinishSource);

            return;
        }

        $targetCart          = $this->getTargetCart($router);
        $displayFinishTarget = $targetCart->displayFinishTarget();
        if ($displayFinishTarget['result'] != 'success') {
            $this->responseAjaxJson($displayFinishTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $displayFinish = $router->displayFinish();
        $this->_notice = $router->getNotice();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->responseAjaxJson($displayFinish);

        return;
    }

    /**
     * TODO: IMPORT
     */

    protected function prepareImport()
    {
        $router = Bootstrap::getModel('cart');
        $this->_initNotice($router);

        $sourceCart          = $this->getSourceCart($router);
        $prepareImportSource = $sourceCart->prepareImportSource();
        if ($prepareImportSource['result'] != 'success') {
            $this->responseAjaxJson($prepareImportSource);
            return;
        }
        $this->_notice = $sourceCart->getNotice();

        $targetCart          = $this->getTargetCart($router);
        $prepareImportTarget = $targetCart->prepareImportTarget();
        if ($prepareImportTarget['result'] != 'success') {
            $this->responseAjaxJson($prepareImportTarget);

            return;
        }
        $this->_notice = $targetCart->getNotice();

        $router->setNotice($this->_notice);
        $prepareImport = $router->prepareImport();
        $this->_notice = $router->getNotice();

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->responseAjaxJson($prepareImport);

        return;
    }

    protected function clear()
    {
        $response = $this->defaultResponse();
        $router   = Bootstrap::getModel('cart');
        $this->_initNotice($router);

        $targetCart = $this->getTargetCart($router);
        $clearData  = $targetCart->clearData();
//         var_dump($clearData);exit;

        $this->_notice = $targetCart->getNotice();

        if ($clearData['result'] == 'success' && $this->_notice['config']['taxes']) {
            $sourceCart    = $this->getSourceCart($router);
            $prepareSource = $sourceCart->prepareTaxesExport();
            $this->_notice = $sourceCart->getNotice();
            $targetCart->setNotice($this->_notice);
            $prepareTarget                                   = $targetCart->prepareTaxesImport();
            $this->_notice                                   = $targetCart->getNotice();
            $this->_notice['process']['taxes']['time_start'] = time();
            $this->_notice['resume']['process']              = 'import';
            $this->_notice['resume']['type']                 = 'taxes';
        }

        $save_notice = $this->saveNotice($router);
        if (!$save_notice) {
            $this->responseAjaxJson($router->errorDatabase());

            return;
        }

        $this->responseAjaxJson($clearData);

        return;
    }

    /**
     *
     */
    protected function import()
    {
        $response = $this->defaultResponse();
        $cart     = Bootstrap::getModel('cart');
        $this->_initNotice($cart);
        $type = $this->getParam('type', '');
        if (!$type) {
            $response['result']           = 'success';
            $response['msg']              = $cart->consoleSuccess('Finished migration!');
            $response['process']['point'] = 100;
            $this->responseAjaxJson($response);

            return;
        }
        $response['result']                 = 'process';
        $response['process']['next']        = $type;
        $this->_notice['running']           = true;
        $this->_notice['resume']['process'] = 'import';
        $this->_notice['resume']['type']    = $type;
        if (!$this->_notice['config'][$type]) {
            $next_action = $this->_nextAction[$type];
            if ($next_action) {
                if ($this->_notice['config'][$next_action]) {
                    $sourceCart        = $this->getSourceCart($cart);
                    $targetCart        = $this->getTargetCart($cart);
                    $fn_prepare_source = 'prepare' . ucfirst($next_action) . 'Export';
                    $fn_prepare_target = 'prepare' . ucfirst($next_action) . 'Import';
                    $prepareSource     = $sourceCart->$fn_prepare_source();
                    $this->_notice     = $sourceCart->getNotice();
                    $targetCart->setNotice($this->_notice);
                    $prepareTarget = $targetCart->$fn_prepare_target();
                    $this->_notice = $targetCart->getNotice();
                }
                $this->_notice['process'][$next_action]['time_start'] = time();
                $this->_notice['resume']['type']                      = $next_action;
                $response['process']['next']                          = $next_action;
            } else {
                $this->_notice['running'] = false;
                $response['result']       = 'success';
                $response['msg']          .= $cart->consoleSuccess('Finished migration!');
            }

            $save_notice = $this->saveNotice($cart);
            if (!$save_notice) {
                $this->responseAjaxJson($cart->errorDatabase());

                return;
            }
            $save_recent = $this->saveRecent($cart);
            if (!$save_recent) {
                $this->responseAjaxJson($cart->errorDatabase());

                return;
            }
            $this->responseAjaxJson($response);

            return;
        }

        $total         = $this->_notice['process'][$type]['total'];
        $imported      = $this->_notice['process'][$type]['imported'];
        $error         = $this->_notice['process'][$type]['error'];
        $id_src        = $this->_notice['process'][$type]['id_src'];
        $simple_action = $this->_simpleAction[$type];
        $next_action   = $this->_nextAction[$type];
        if ($imported < $total) {
            $fn_get_main        = 'get' . ucfirst($type) . 'MainExport';
            $fn_get_ext         = 'get' . ucfirst($type) . 'ExtExport';
            $fn_get_id          = 'get' . ucfirst($simple_action) . 'IdImport';
            $fn_check_export    = 'check' . ucfirst($simple_action) . 'Import';
            $fn_convert_export  = 'convert' . ucfirst($simple_action) . 'Export';
            $fn_router_import   = 'router' . ucfirst($simple_action) . 'Import';
            $fn_before_import   = 'before' . ucfirst($simple_action) . 'Import';
            $fn_import          = $simple_action . 'Import';
            $fn_after_import    = 'after' . ucfirst($simple_action) . 'Import';
            $fn_addition_import = 'addition' . ucfirst($simple_action) . 'Import';
            $sourceCart         = $this->getSourceCart($cart);
            $targetCart         = $this->getTargetCart($cart);

            $mains = $sourceCart->$fn_get_main();
            if ($mains['result'] != 'success') {
                $this->responseAjaxJson($mains);

                return;
            }

            $ext = $sourceCart->$fn_get_ext($mains);
            if ($ext['result'] != 'success') {
                $this->responseAjaxJson($ext);

                return;
            }
            foreach ($mains['data'] as $main) {
                if ($imported >= $total) {
                    break;
                }

                $imported++;
                $convert = $sourceCart->$fn_convert_export($main, $ext);


                if ($convert['result'] == 'error') {
                    $this->responseAjaxJson($convert);

                    return;
                }
                if ($convert['result'] == 'warning') {
                    $error++;
                    $response['msg'] .= $convert['msg'];
                    if (isset($convert['id'])) {
                        $id_src = $convert['id'];
                        Bootstrap::log($convert['id'], $type.'_error');


                    }
                    continue;
                }

                if ($convert['result'] == 'pass') {
                    continue;
                }

                $convert_data = $convert['data'];




                if (Bootstrap::getConfig('dev_mode')) {
                    Bootstrap::log($convert_data, $type);
                }
                $id_src = $targetCart->$fn_get_id($convert_data, $main, $ext);
                if ($targetCart->$fn_check_export($convert_data, $main, $ext)) {
                    continue;
                }
                // router - before - addition

                $import = $targetCart->$fn_import($convert_data, $main, $ext);

                if ($import['result'] == 'error') {
                    $this->responseAjaxJson($import);

                    return;
                }
                if ($import['result'] != 'success') {
                    $error++;
                    $response['msg'] .= $import['msg'];
                    continue;
                }
                $id_desc = $import['data'];
//                if(isset($import['rootCate'])){
//                    $this->_notice['map']['categoryData'][$import['rootCate']] = $import['data'];
//                    $this->saveNotice($cart);
//                }
                $afterImport = $targetCart->$fn_after_import($id_desc, $convert_data, $main, $ext);
                if ($afterImport['result'] == 'error') {
                    $this->responseAjaxJson($afterImport);

                    return;
                }
                if ($afterImport['result'] == 'success' && $afterImport['msg']) {
//                    $error++;
                    $response['msg'] .= $afterImport['msg'];
                }

            }
            $this->_notice['process'][$type]['point'] = $this->_getPoint($total, $imported);
            $response['process']['type']              = $type;
        } else {
            $msg_time        = $cart->createTimeToShow(time() - $this->_notice['process'][$type]['time_start']);
            $response['msg'] .= $cart->consoleSuccess('Finished importing ' . $type . '! Run time: ' . $msg_time);
            $response['msg'] .= $cart->getMsgStartImport($next_action);
            if ($next_action) {
                if ($this->_notice['config'][$next_action]) {
                    $sourceCart        = $this->getSourceCart($cart);
                    $targetCart        = $this->getTargetCart($cart);
                    $fn_prepare_source = 'prepare' . ucfirst($next_action) . 'Export';
                    $fn_prepare_target = 'prepare' . ucfirst($next_action) . 'Import';
                    $prepareSource     = $sourceCart->$fn_prepare_source();
                    $this->_notice     = $sourceCart->getNotice();
                    $targetCart->setNotice($this->_notice);
                    $prepareTarget = $targetCart->$fn_prepare_target();
                    $this->_notice = $targetCart->getNotice();
                }
                $this->_notice['process'][$next_action]['time_start'] = time();
                $this->_notice['resume']['type']                      = $next_action;
                $response['process']['next']                          = $next_action;
            } else {
                $this->_notice['running'] = false;
                $response['result']       = 'success';
            }
            $this->_notice['process'][$type]['point'] = $this->_getPoint($total, $imported, true);
        }

        $this->_notice['process'][$type]['imported'] = $imported;
        $this->_notice['process'][$type]['error']    = $error;
        $this->_notice['process'][$type]['id_src']   = $id_src;

        $responseType = array('total', 'imported', 'error', 'point');
        foreach ($responseType as $response_type) {
            $response['process'][$response_type] = $this->_notice['process'][$type][$response_type];
        }
        $save_notice = $this->saveNotice($cart);
        if (!$save_notice) {
            $this->responseAjaxJson($cart->errorDatabase());

            return;
        }
        $save_recent = $this->saveRecent($cart);
        if (!$save_recent) {
            $this->responseAjaxJson($cart->errorDatabase());

            return;
        }
        $this->responseAjaxJson($response);

        return;

    }

    /**
     * TODO: EXTENDS
     */

    protected function _getPoint($total, $import, $finish = false)
    {
        if (!$finish && $total == 0) {
            return 0;
        }
        if ($total <= $import) {
            $point = 100;
        } else {
            $percent = $import / $total;
            $point   = number_format($percent, 2) * 100;
        }

        return $point;
    }
}