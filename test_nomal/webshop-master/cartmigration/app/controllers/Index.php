<?php

class LECM_Controller_Index
    extends LECM_Controller
{
    public function index()
    {
        $this->addJs(array(
            'pub/js/jquery.validate.min.js',
            'pub/js/jquery.form.min.js',
            'pub/js/cartmigration.js',
        ));
        $this->_initNotice();
        $this->_render('migration.tpl');
    }
}