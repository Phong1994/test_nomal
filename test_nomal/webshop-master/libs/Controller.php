<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 31/01/2018
 * Time: 16:53
 */

class Controller
{
    const KEY_ADMIN_TOKEN = 'admin_token';
    const KEY_TOKEN = 'fo_token';

    protected $_cssFile = array();
    protected $_jsFile = array();
    protected $_breadCrumbs = array();
    protected $_title;
    protected $_error;

    public function prepareProcess()
    {
        if (!Bootstrap::isSetup()) {
            if (empty($_POST)) {

                $this->_redirect('setup');

                return;
            }
        }
        $this->addCss($this->getCssDefault())
             ->addJs($this->getJsDefault());
    }

    public function beforeRenderLayout()
    {
        $error = $this->getErrorSession();
        if ($error) {
            $this->_error = $error;
        }
    }

    public function renderLayout()
    {
        $this->beforeRenderLayout();

    }
    public function run()
    {
        $this->renderLayout();
    }
    protected function _render($name = null)
    {
        if (!$name) {
            $name = 'home';
        }
        $layout = Bootstrap::getLayout($name);
        include $layout;
    }

    protected function getCssDefault()
    {
        return array(
            'pub/css/bootstrap.min.css',
            'pub/css/bootstrap-theme.min.css',
            'pub/css/style.css',
            'pub/css/font-awesome.min.css'
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
            'pub/js/jquery.validate.min.js',
            'pub/js/jquery.form.min.js',
            'pub/js/webshop.js',
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

    protected function _redirect($url = null, $error = '')
    {
        $base_url = Bootstrap::getUrl();
        if (!$url) {
            $url = $base_url;
        } else {
            $url = $base_url . '/' . $url;
        }
        if ($error) {
            $this->setErrorSession($error);
        }
        header("Location: " . $url);
    }
    protected function _redirectAdmin($url = null, $error = '')
    {
        $base_url = Bootstrap::getUrlAdmin();
        if (!$url) {
            $url = $base_url;
        } else {
            $url = $base_url . '/' . $url;
        }
        if ($error) {
            $this->setErrorSession($error);
        }
        header("Location: " . $url);
    }
    protected function generateHeader()
    {
        $header = '<link rel="shortcut icon" href="'.Bootstrap::getImages('help/logo.png').'" />';
        $header .= '<title>' . $this->getTitle() . '</title>';

        $cssFile = $this->getGlobal('cssFile');
        foreach ($cssFile as $css_file) {

            $header .= '<link type="text/css" rel="stylesheet" media="screen" href="' . Bootstrap::getUrl($css_file) . '"/>';
        }


        $jsFile = $this->getGlobal('jsFile');
        foreach ($jsFile as $js_file) {

            $header .= '<script type="text/javascript" src="' . Bootstrap::getUrl($js_file) . '"></script>';
        }

        $header .= '<script type="text/javascript">
    $(document).ready(function () {
        $.Webshop({
            url: "' . Bootstrap::getUrl() . '/",
        });
    });
</script>';

        return $header;
    }

    protected function getGlobal($key, $default = '')
    {
        $key      = '_' . $key;
        $variable = isset($this->$key) ? $this->$key : $default;

        return $variable;
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

    protected function responseAjaxJson($data)
    {
        echo json_encode($data);
        exit();
    }

    protected function defaultResponse()
    {
        return array(
            'result' => '',
            'msg'    => '',
            'data'   => '',
        );
    }

    protected function responseSuccess($data = '', $msg = '')
    {
        $response = array(
            'result' => 'success',
            'msg'    => $msg,
            'data'   => $data,
        );
        $this->responseAjaxJson($response);
    }

    protected function returnSuccess($data = '', $msg = '')
    {
        return array(
            'result' => 'success',
            'msg'    => $msg,
            'data'   => $data,
        );
    }

    protected function returnError($data = '', $msg = '')
    {
        return array(
            'result' => 'error',
            'msg'    => $msg,
            'data'   => $data,
        );
    }

    protected function responseError($data = '', $msg = '')
    {
        $response = array(
            'result' => 'error',
            'msg'    => $msg,
            'data'   => $data,
        );
        $this->responseAjaxJson($response);
    }

    protected function responseWarning($data = '', $msg = '')
    {
        $response = array(
            'result' => 'warning',
            'msg'    => $msg,
            'data'   => $data,
        );
        $this->responseAjaxJson($response);
    }

    public function getConfig($key, $default = '')
    {
        return Bootstrap::getConfig($key, $default);
    }

    public function setConfig($key, $data)
    {
        return Bootstrap::setConfig($key, $data);
    }

    public function unsetConfig($key)
    {
        return Bootstrap::unsetConfig($key);
    }

    public function passwordHash($password)
    {
        return md5($password);
    }

    public function getNewDate($time = null)
    {
        if ($time) {
            return date("Y-m-d H:i:s", $time);
        }

        return date("Y-m-d H:i:s");
    }

    public function redirect404()
    {
        $this->_render('404');
        exit;
    }

    public function getTitle()
    {
        if ($this->_title) {
            $titles = explode(' ', $this->_title);
            foreach ($titles as $key => $title) {
                $titles[$key] = ucfirst($title);
            }

            return implode(' ', $titles);
        }

        return '';
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getErrorSession()
    {
        return Session::getKey(Bootstrap::getConfigIni('key_admin_error'), '');
    }

    public function setErrorSession($error)
    {
        Session::setKey(Bootstrap::getConfigIni('key_admin_error'), $error);
    }

    public function unsetErrorSession()
    {
        Session::unsetKey(Bootstrap::getConfigIni('key_admin_error'));
    }

    public function getError()
    {
        return $this->getGlobal('error', '');
    }

    public function setError($error)
    {
        $this->_error = $error;
    }
    public function setBreadCrumbs($breadCrumbs){
        $this->_breadCrumbs = $breadCrumbs;
    }
    public function getBreadCrumbs(){
        return $this->_breadCrumbs;
    }

}