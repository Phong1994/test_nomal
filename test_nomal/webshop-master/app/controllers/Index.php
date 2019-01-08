<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:12
 */

class Controller_Index
    extends Controller
{
    public function run()
    {
        $this->_render('home');
    }
}