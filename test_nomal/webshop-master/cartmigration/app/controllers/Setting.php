<?php

class LECM_Controller_Setting
    extends LECM_Controller
{
    public $_setting;

    public function index()
    {
        $this->addJs(array(
            'pub/js/jquery.validate.min.js',
        ));
        $cart = Bootstrap::getModel('cart');
        $configs = array(
            'storage',
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
            'cartrules',
            'delay',
            'retry',
            'src_prefix',
            'target_prefix',
            'license',
        );
        if(!empty($_POST)){
            foreach($configs as $config){
                $value = isset($_POST[$config]) ? $_POST[$config] : '';
                $cart->saveSetting($config, $value);
            }
            $this->_setMessage('Settings saved.', 'success');
        }
        foreach($configs as $config){
            $value = $cart->getSetting($config, '');
            $this->_setting[$config] = $value;
        }
        $this->_render('setting.tpl');
    }

    protected function getSetting($key)
    {
        $value = isset($this->_setting[$key]) ? $this->_setting[$key] : '';
        return $value;
    }
}