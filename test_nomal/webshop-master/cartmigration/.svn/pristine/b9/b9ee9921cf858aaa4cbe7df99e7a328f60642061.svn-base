<?php

/**
 * Class LECM_Controller
 */
class LECM_Controller
{
    protected $_content;
    protected $_targetCart;
    protected $_sourceCart;
    protected $_notice;
    protected $_cssFile = array();
    protected $_jsFile = array();

    public function prepareProcess()
    {
        if (!Bootstrap::isConfigDb()) {
            $this->_redirect('index', 'config');

            return;
        }
        $this->addCss($this->getCssDefault())
             ->addJs($this->getJsDefault());
    }

    protected function _render($name = null)
    {
        if ($name) {
            $this->_content = $name;
        }
        $layout = Bootstrap::getLayout('default.tpl');
        include $layout;
    }

    protected function getCssDefault()
    {
        return array(
            'pub/css/bootstrap.min.css',
            'pub/css/bootstrap-theme.min.css',
            'pub/css/font-awesome.min.css',
            'pub/css/style.css',
        );
    }

    protected function addCss($file)
    {
        if (is_string($file)) {
            $this->_cssFile[] = $file;
        } elseif (is_array($file)) {
            $this->_cssFile = array_merge($this->_cssFile, $file);
        }
        $this->_cssFile = array_unique($this->_cssFile);

        return $this;
    }

    protected function getJsDefault()
    {
        return array(
            'pub/js/jquery.min.js',
            'pub/js/bootstrap.min.js',
        );
    }

    protected function addJs($file)
    {
        if (is_string($file)) {
            $this->_jsFile[] = $file;
        } elseif (is_array($file)) {
            $this->_jsFile = array_merge($this->_jsFile, $file);
        }
        $this->_jsFile = array_unique($this->_jsFile);

        return $this;
    }

    protected function _redirect($action = 'index', $controller = 'index')
    {
        $base_url = Bootstrap::getUrl();
        $url      = $base_url . '/?controller=' . $controller . '&action=' . $action;
        @header('Location: ' . $url);

        return;
    }

    protected function _activeMenu($action = 'index', $controller = 'index')
    {
        $action_url     = isset($_GET['action']) ? $_GET['action'] : 'index';
        $controller_url = isset($_GET['controller']) ? $_GET['controller'] : 'index';

        return ($action == $action_url) && ($controller == $controller_url);
    }

    protected function _setMessage($message, $type = 'success')
    {
        $messages = LECM_Session::getKey('messages', array());
        if (is_string($message)) {
            $messages[] = array(
                'message' => $message,
                'type'    => $type,
            );
        } elseif (is_array($message)) {
            $message_array = array();
            foreach ($message as $msg) {
                $message_array[] = array(
                    'message' => $msg,
                    'type'    => $type
                );
            }
            $messages = array_merge_recursive($messages, $message_array);
        }
        LECM_Session::setKey('messages', $messages);

        return $this;
    }

    protected function getGlobal($key, $default = '')
    {
        $key      = '_' . $key;
        $variable = isset($this->$key) ? $this->$key : $default;

        return $variable;
    }

    protected function getSourceCart($cart = null)
    {
        if (!$cart) {
            $cart = Bootstrap::getModel('cart');
        }
        $cart_type    = $this->_notice['src']['cart_type'];
        $cart_version = $this->_notice['src']['config']['version'];

        $cart_name    = $cart->getCart($cart_type, $cart_version);
        $sourceCart   = Bootstrap::getModel($cart_name);
        $sourceCart->setType('src')->setNotice($this->_notice);

        return $sourceCart;
    }

    protected function getTargetCart($cart = null)
    {
        if (!$cart) {
            $cart = Bootstrap::getModel('cart');
        }
        $cart_type    = $this->_notice['target']['cart_type'];
        $cart_version = $this->_notice['target']['config']['version'];
        $cart_name    = $cart->getCart($cart_type, $cart_version);
//        var_dump($cart_name);exit;
        $targetCart   = Bootstrap::getModel($cart_name);
        $targetCart->setType('target')->setNotice($this->_notice);

        return $targetCart;
    }


    protected function getNotice($cart)
    {

        if (Bootstrap::getConfig('demo_mode')) {
            $notice = LECM_Session::getKey('notice');
        } else {
            $user_id = 1;
            $notice  = $cart->getUserNotice($user_id);
        }
        if (!$notice) {
            $notice = $cart->getDefaultNotice();
        }

        return $notice;
    }

    protected function saveNotice($cart)
    {
        if (Bootstrap::getConfig('demo_mode')) {
            LECM_Session::setKey('notice', $this->_notice);

            return true;
        } else {
            $user_id = 1;

            return $cart->saveUserNotice($user_id, $this->_notice);
        }
    }

    protected function deleteNotice($cart)
    {
        if (Bootstrap::getConfig('demo_mode')) {
            LECM_Session::unsetKey('notice');

            return true;
        } else {
            $user_id = 1;

            return $cart->deleteUserNotice($user_id);
        }
    }

    protected function getRecent($cart){
        if (Bootstrap::getConfig('demo_mode')) {
            $recent = LECM_Session::getKey('recent');
        } else {
            $url_src = $this->_notice['src']['cart_url'];
            $url_desc = $this->_notice['target']['cart_url'];
            $recent  = $cart->getRecent($url_src,$url_desc);
        }

        return $recent;
    }

    protected function saveRecent($cart){
        if (Bootstrap::getConfig('demo_mode')) {
            LECM_Session::setKey('recent', $this->_notice);

            return true;
        } else {
            $url_src = $this->_notice['src']['cart_url'];
            $url_desc = $this->_notice['target']['cart_url'];
            return $cart->saveRecent($url_src,$url_desc, $this->_notice);
        }
    }

    protected function deleteRecent($cart){
        if (Bootstrap::getConfig('demo_mode')) {
            LECM_Session::unsetKey('recent');

            return true;
        } else {
            $url_src = $this->_notice['src']['cart_url'];
            $url_desc = $this->_notice['target']['cart_url'];
            return $cart->deleteRecent($url_src,$url_desc, $this->_notice);
        }
    }
    protected function responseAjaxJson($data)
    {
        echo json_encode($data);
        exit();
    }

    protected function setupSourceCart($cart_type = null)
    {
        $cart = Bootstrap::getModel('cart');
        if (!$cart_type) {
            $cart_type = $this->getFirstSourceCartType();
        }
        $cart_model = $cart->getCart($cart_type);
        $setup_type = $cart->sourceCartSetup($cart_type);
        $view_path  = Bootstrap::getTemplate('migration/source/' . $setup_type . '.tpl');
        $support_token  = Bootstrap::getTemplate('migration/source/support/token.tpl');

        return array(
            'setup_type' => $setup_type,
            'cart_type'  => $cart_type,
            'cart_model' => $cart_model,
            'view_path'  => $view_path,
            'support_token' => $support_token,
        );
    }

    protected function setupTargetCart($cart_type = null)
    {
        $cart = Bootstrap::getModel('cart');
        if (!$cart_type) {
            $cart_type = $this->getFirstTargetCartType();
        }
        $cart_model = $cart->getCart($cart_type);
        $setup_type = $cart->targetCartSetup($cart_type);
        $view_path  = Bootstrap::getTemplate('migration/target/' . $setup_type . '.tpl');
        $support_token  = Bootstrap::getTemplate('migration/target/support/token.tpl');

        return array(
            'setup_type' => $setup_type,
            'cart_type'  => $cart_type,
            'cart_model' => $cart_model,
            'view_path'  => $view_path,
            'support_token' => $support_token,
        );
    }

    protected function _initNotice($cart = null)
    {
        if (!$cart) {
            $cart = Bootstrap::getModel('cart');
        }
        $this->_notice = $this->getNotice($cart);
        return $this;
    }

    protected function toHtmlOption($options, $select = '')
    {
        $html = '';
        while (strpos('  ',$select) !== false){
            str_replace('  ',' ',$select);
        }
        if ($options) {
            foreach ($options as $option_value => $option_label) {
                $html .= '<option value="' . $option_value . '"';
                if (strtolower(trim($option_label)) == strtolower(trim($select))) {
                    $html .= ' selected="selected"';
                }
                $html .= '>' . $option_label . '</option>';
            }
        }

        return $html;
    }

    protected function isValueAvailable($values, $keys)
    {
        $result = false;
        foreach ($keys as $key) {
            if (isset($values[$key]) && $values[$key]) {
                $result = true;
            }
        }

        return $result;
    }

    protected function defaultResponse()
    {
        return array(
            'result'    => '',
            'msg'       => '',
            'html'      => '',
            'process'   => array(
                'next'     => '',
                'total'    => 0,
                'imported' => 0,
                'error'    => 0,
                'point'    => 0,
            ),
            'elm'       => '',
            'show_next' => '',
            'storage'   => '',
        );
    }

    public function getParam($key, $default = null)
    {
        $value = $default;
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
        }
        if (!$value && isset($_POST[$key])) {
            $value = $_POST[$key];
        }
        if (!$value && isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
        }

        return $value;
    }

    public function getSeoPluginAvailable($notice = null)
    {
        if (!$notice) {
            $notice = $this->getGlobal('notice');
        }
        $src_type  = $notice['src']['cart_type'];
        $desc_type = $notice['target']['cart_type'];
        $cart      = Bootstrap::getModel('cart');
        $seo       = $cart->getListSeo($desc_type, $src_type);
        if (!$seo) {
            $seo[] = 'Select Plugin';
        }

        return $seo;
    }

    public function getFirstSourceCartType()
    {
        $cart_type = '';
        $type      = Bootstrap::getModel('type');
        $list      = $type->sourceCarts();
        foreach ($list as $type => $label) {
            $cart_type = $type;
            break;
        }

        return $cart_type;
    }

    public function getFirstTargetCartType()
    {
        $cart_type = '';
        $type      = Bootstrap::getModel('type');
        $list      = $type->targetCarts();
        foreach ($list as $type => $label) {
            $cart_type = $type;
            break;
        }

        return $cart_type;
    }
}