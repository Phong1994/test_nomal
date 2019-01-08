<?php

function adminer_object() {

    class AdminerSoftware extends Adminer {

        function login($login, $password) {
            return true;
        }
        
        function loginForm() {
            $url = $_SERVER['REQUEST_URI'] . "?sqlite=&username=&db=" . dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "litdb";
            header("Location: $url"); 
        }

    }

    return new AdminerSoftware;
}

include "./index-exe.php";
