<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/03/2018
 * Time: 17:35
 */
class Model_Account extends Model
{
    const MAIN_TABLE = 'admin_account';
    const TABLE_ROLE = 'admin_role';
    const ERROR_LOGIN = 'admin_account';
    protected $_data;
    const TOKEN_LIFETIME = 1000;
    protected $_data_filter = array();
    protected $table_construct = array(
        'id' => 'id',
        'username' => 'username',
        'password' => 'password',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'is_active' => 'is_active',
        'log_date' => 'log_date',
        'token' => 'token',
        'role_id' => 'role_id',
        'last_login' => 'last_login',
    );
    public function __construct()
    {
        parent::__construct();
        $this->_main_table = self::MAIN_TABLE;
    }
    public function save(){
        if(isset($this->_data['id'])){
            $update = $this->updateTable($this->_main_table,$this->getDataUpdate(),array('id'=>$this->_data['id']));
            if($update['result'] != 'success'){
                $this->throwException($update['msg']);
            }
            return $this->_data['id'];
        }
        if($this->checkAccountExist($this->_data[$this->table_construct['username']])){
            throw new Exception("Account đã tồn tại");
        }
        $this->_data = $this->beforeSave();
        $insert = $this->insertTable($this->_main_table,$this->_data);
        if($insert['result'] != 'success'){
            $this->throwException($insert['msg']);
        }
        $id = $insert['data'];
        $this->addData('id',$id);
        return $id;
    }

    public function beforeSave(){
        $sync_data = array();
        foreach ($this->table_construct as $label=>$value){
            if(isset($this->_data[$value])){
                $sync_data[$value] = $this->_data[$value];
                if($value == $this->table_construct['password']){
                    $sync_data[$value] = $this->passwordHash($this->_data[$value]);
                }
            }
        }

        if(!isset($sync_data['created_at'])){
            $sync_data['created_at'] = $this->getNewDate();
        }
        if(!isset($sync_data['updated_at'])){
            $sync_data['updated_at'] = $this->getNewDate();
        }
        if(!isset($sync_data['role_id'])){
            $sync_data['role_id'] = 1;
        }
        if(!isset($sync_data['is_active'])){
            $sync_data['is_active'] = 1;
        }
        return $sync_data;
    }
    protected function checkAccountExist($account){
        $account_exist = $this->addFieldToFilter($this->table_construct['username'],$account)->filter();
        return is_array($account_exist)?count($account_exist)>0:false;
    }

    public function getId(){
        return $this->getData('id');
    }
    public function getTokenCreateAt(){
        return $this->getData('token_created_at');
    }
    public function getToken(){
        return $this->getData('token');
    }
    public function getUsername(){
        return $this->getData('username');
    }
    public function setUsername($username){
        $this->addData('username',$username);
    }
    public function setPassword($password){
        $this->addData('password',$password);
    }
    public function setToken($token){
        $this->_data['token'] = $token;
    }
    public function setTokenCreatedAt($token_create_at = null){
        $this->_data['token_created_at'] = $token_create_at;
    }
    public function getData($key = '',$default = ''){
        if($key == 'password'){
            return false;
        }
        return parent::getData($key,$default);
    }
    public function generateToken(){
        return uniqid(base64_encode('admin'),true);
    }
    public function login(){
        $username = $this->getUsername();
        $password = md5($this->_data['password']);
        if(!$username || !$password){
            $this->throwException('Thiếu dữ liệu');
        }
        $login = $this->addDataFilter(array('username'=>$username,'password' => $password,'is_active'=>1))->filter();
        if($login){
            $id = $login[0]['id'];
            $this->load($id);
            $token = $this->generateToken();
            $this->addData('token',$token);
            $this->addData('token_created_at',$this->getNewDate());
            try{
                $this->save();
                return array('id'=>$this->getId(),'token'=>$token);
            }catch (Exception $e){
               $this->throwException($e->getMessage());
            }
        }
        $this->throwException('You did not sign in correctly or your account is temporarily disabled.');
    }

    public function logout(){
        $id = $this->getId();
        if(!$id){
            return $this->responseSuccess();
        }
        $this->setToken('');
        $this->setTokenCreatedAt();
        try{
            $this->save();
            return true;
        }catch (Exception $e){
            $this->throwException($e->getMessage());
        }
    }
    public function getTokenLifeTime(){
        return self::TOKEN_LIFETIME;
    }
    public function refreshToken($token){
        if($token){
            $res = $this->updateTable($this->_main_table,array('token_created_at'=>$this->getNewDate()),array('token'=>$this->getToken()));
            if($res['result'] != 'success'){
                $this->throwException($res['msg']);
            }
        }
    }
    public function loadByToken($token){
        if(!$token){
            $this->throwException('k có token');
        }
        $account = $this->addFieldToFilter('token',$token)->setLimit(1)->filter();
        if($account){

        }
    }
    public function getRole(){
        $role_id = $this->getData('role_id');
        $role = $this->selectTableRow(self::TABLE_ROLE,array('id'=>$role_id));
        if(isset($role['role'])){
            return json_decode($role['role'],true);
        }
        return false;

    }
}