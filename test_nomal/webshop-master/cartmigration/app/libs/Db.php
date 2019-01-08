<?php

class LECM_Db
{
    protected static $_db = null;

    public static function getInstance($config)
    {
        if(!static::$_db){
            $class_name = 'LECM_Db_Sqlite';
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