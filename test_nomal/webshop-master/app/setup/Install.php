<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:43
 */

class Setup_Install extends Controller
{

    public function run($db){
        $install = array(
            'category',
            'product',
            'customer',
            'order',
            'extend'
        );
        $version_install = Bootstrap::getVersionInstall();

        if(version_compare($version_install, '1.0.0') < 0){

        }

        return $this;

    }
    public function install($db){

    }

}