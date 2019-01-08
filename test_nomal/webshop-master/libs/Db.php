<?php

class Libs_Db
{
    protected static $_db = null;

    public static function getInstance($config)
    {
        if(!static::$_db){
            $class_name = '';
            if(function_exists('mysqli_connect')){
                $class_name = 'Libs_Db_Mysqli';
            } else {
                $class_name = 'Libs_Db_Mysql';
            }
            $db = new $class_name();
            $db->setConfig($config);
            $connect = $db->connect();
            if(!$connect){
                return null;
            }
            static::$_db = $db;
        }
        return static::$_db;
    }
}