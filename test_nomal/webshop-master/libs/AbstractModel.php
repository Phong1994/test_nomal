<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02/03/2018
 * Time: 10:40
 */

interface Abstract_Model
{
    public function addData($key,$value);
    public function addDataFilter($data);
    public function addFieldToFilter($key,$value);
    public function filter();
    public function getData($key = '',$default = '');
    public function load($id);
    public function save();
    public function setData($data);
    public function beforeSave();
}