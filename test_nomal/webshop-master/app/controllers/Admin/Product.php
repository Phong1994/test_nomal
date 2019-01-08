<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:12
 */

class Controller_Admin_Product
    extends Controller
{
    protected $_id;
    public function _construct($id)
    {
        $this->_id = $id;
    }
    public function __construct()
    {
    }
    public function run()
    {
        echo $this->_id;
        $this->_render('home');
    }
}