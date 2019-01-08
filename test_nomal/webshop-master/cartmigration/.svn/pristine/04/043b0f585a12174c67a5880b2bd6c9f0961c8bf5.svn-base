<?php

class LECM_Detected
{
    public static function getBaseUrl()
    {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL = "https://";
        } else
            $pageURL = "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . dirname($_SERVER["SCRIPT_NAME"]);
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . dirname($_SERVER["SCRIPT_NAME"]);
        }
        return $pageURL;
    }

}