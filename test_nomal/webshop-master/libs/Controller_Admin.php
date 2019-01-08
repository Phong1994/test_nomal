<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10/03/2018
 * Time: 09:33
 */
class Controller_Admin extends Controller{
    protected $_type;
    public function __construct()
    {

    }
    public function getUser(){
        $token = Session::getKey(self::KEY_ADMIN_TOKEN);
        if($token){
            $model_account = Bootstrap::getModel('account');
            $model_account->getTotalNumberPage();
            $filter = $model_account->addFieldToFilter('token',$token)->setLimit(1)->filter();
            if($filter){
                $model_account->setData($filter);
                $token_date = $model_account->getTokenCreateAt();
                $token_time = strtotime($token_date);
                $now_date = time();
                if($now_date - $token_time > $model_account->getTokenLifeTime()){
                    Session::unsetKey(self::KEY_ADMIN_TOKEN);
                    $this->_redirectAdmin('user/login','Đăng nhập hết hạn');
                }
                return $model_account;
            }
        }
        return false;
    }
    public function refreshAdminToken()
    {
        $token = Session::getKey(self::KEY_ADMIN_TOKEN);
        if ($token) {
            $model_account = Bootstrap::getModel('account');
            try {
                $model_account->refreshToken($token);

                return $this->returnSuccess();
            } catch (Exception $e) {
                return $this->returnError(null, $e->getMessage());
            }
        }
    }
    public function getDataAccount($key = '',$default = ''){
        $model_account = $this->getUser();
        if($model_account){
            return $model_account->getData($key,$default);
        }
        return $default;
    }
    public function getRole(){
        $model_account = $this->getUser();
        return $model_account->getRole();
    }
}