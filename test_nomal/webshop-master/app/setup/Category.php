<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:46
 */

class Setup_Install_Category extends Model
{
    const TABLE_CATEGORY = 'category';
    public function install($function)
    {
        if(method_exists($this,$function)){
            return $this->$function();
        }else{
            return array(
                'result' => 'success',
            );
        }
    }


    public function cate(){
        return array(
            'table' => 'cate',
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'cate_id' => 'BIGINT NOT NULL',
            ),
            'references' => array(
                'cate_id' => array(
                    'table' =>self::TABLE_CATEGORY,
                    'row' => 'id',
                ),
            ),
        );
    }

    public function createCateTable(){
        $query = $this->arrayToCreateTableSql($this->cate());
        if($query['result'] != 'success' || !isset($query['query'])){
            return $this->errorConnectDatabase();
        }
        $create = $this->queryRaw($query['query']);
        if($create['result'] == 'success'){
            return array(
                'result' => 'success',
                'msg' => $this->consoleSuccess('create table '.$this->getTableName('cate').' success'),

                'data' => array(
                    'status' => 'success',
                    'function' => '',
                ),
            );
        }
        return $this->errorConnectDatabase();
    }

    public function createCategoryTable(){
        $table_construct = array(
            'table' => self::TABLE_CATEGORY,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'name' => 'VARCHAR(255) NOT NULL',
                'status' => 'TINYINT(2)',
                'parent_id' => 'BIGINT NOT NULL',
                'level' => 'INT(11) NOT NULL',
                'updated_at' => 'DATETIME',
                'created_at' => 'DATETIME',
                'description' => 'TEXT',
                'url_key' => 'VARCHAR(255) NOT NULL',
                'position' => 'INT(11)',
                'path' => 'VARCHAR(255)',
                'product_count' => 'INT(11)'
            ),
            'unique' => array(
                array(
                    'name',
                ),
                array(
                    'url_key'
                ),
            ),
        );
        $result = $this->createTableQuery($table_construct,'createCategoryTable',true);
        if($result['result'] == 'success'){
            $add_data = $this->addDataDefaultCategory();
            if($add_data['result'] != 'success'){
                return $add_data;
            }
        }
        return $result;
    }
    function addDataDefaultCategory(){
        $insert_data = array(
            0 => array(
                'id' => 1,
                'name' => 'Default Category',
                'status' => '1',
                'parent_id' => '0',
                'level' => '0',
                'updated_at' => $this->getNewDate(),
                'created_at' => $this->getNewDate(),
                'url_key' => 'default-category',
                'position' => '0',
                'path' => '1',
                'product_count' => '0',
            ),
        );
        $truncate = $this->truncateTable(self::TABLE_CATEGORY);
        if(!$truncate || $truncate['result'] != 'success'){
            return $this->errorConnectDatabase($truncate['msg']);
        }
        foreach ($insert_data as $key=>$data){
            $res = $this->insertTable(self::TABLE_CATEGORY,$data);
            if(!$res || $res['result'] != 'success'){
                return $this->errorConnectDatabase($res['msg']);
            }
        }
        return array(
            'result' => 'success',
            'msg' => '',
        );
    }
}