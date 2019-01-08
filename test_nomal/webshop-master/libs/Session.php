<?php

class Session
{
    public static function init()
    {
        $session_name = Bootstrap::getConfigIni('session_name');
        ini_set('session.gc_maxlifetime', 86400);
        session_set_cookie_params(86400);
        session_name($session_name);
        session_start();
    }

    public static function getKey($key, $default = '')
    {
        $value = isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        return $value;
    }

    public static function setKey($key, $value = '')
    {
        $_SESSION[$key] = $value;
    }

    public static function unsetKey($key)
    {
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    public static function isLogin()
    {
        return self::getKey('token', false);
    }

    public static function login($data)
    {
        foreach($data as $key => $value){
            self::setKey($key, $value);
        }
    }

    public static function logout()
    {
        $defaults = self::defaultValue();
        foreach($defaults as $key => $value){
            self::unsetKey($key);
        }
    }

    public static function refresh()
    {
        $_SESSION = $_SESSION;
        return true;
    }

    public static function defaultValue()
    {
        return array(
            'token' => '',
            'username' => '',
            'name' => '',
            'id' => 0,

        );
    }
}
