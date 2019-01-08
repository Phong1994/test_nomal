<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 06/03/2018
 * Time: 16:59
 */

class Model_UrlReWrite extends Model
{
    const MAIN_TABLE = 'url_rewrite';
    protected $table_construct = array(
        'id' => 'id',
        'parent_id' => 'parent_id',
        'type' => 'type',
        'url' => 'url',
        'controller' => 'controller',
        'redirect_type' => 'redirect_type',
        'is_default' => 'is_default',
    );
    public function __construct()
    {
        parent::__construct();
        $this->_main_table = self::MAIN_TABLE;
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
        if(!isset($sync_data['redirect_type'])){
            $sync_data['redirect_type'] = 0;
        }
        if(!isset($sync_data['is_default'])){
            $sync_data['is_default'] = 0;
        }
        return $sync_data;
    }
    public function getParentId(){
        return $this->getData('parent_id');
    }
    public function getUrl(){
        return $this->getData('url');
    }
    public function getController(){
        return $this->getData('controller');
    }
    public function getType(){
        return $this->getData('type');
    }
    public function getRedirectType(){
        return $this->getData('redirect_type');
    }
    public function getIsDefault(){
        return $this->getData('is_default');
    }

}