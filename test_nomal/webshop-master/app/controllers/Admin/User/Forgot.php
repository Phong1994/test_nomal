<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07/03/2018
 * Time: 17:40
 */
class Controller_Admin_User_forgot
    extends Controller
{
    const TITLE = 'forgot password';
    public function __construct()
    {
        $this->_title = self::TITLE;
    }
    public function run()
    {
        $this->_render('admin/user/forgot');
    }
    public function isLogin(){
        $token = Session::getKey('admin_token');
        if($token){
            $model_account = Bootstrap::getModel('account');
            $filter = $model_account->addFieldToFilter('token',$token)->filter();
            if($filter){
                $id = $filter[0]['id'];
                return $id;
            }
        }
        return false;
    }
    public function forgotPassword(){

    }
    public function login(){

    }
}