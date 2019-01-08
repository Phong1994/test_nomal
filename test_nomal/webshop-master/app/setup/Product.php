<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:46
 */

class Setup_Install_Product extends Model
{
    const TABLE_ATTRIBUTE = 'product_attribute';
    const TABLE_ATTRIBUTE_SET = 'attribute_set';
    const TABLE_ATTRIBUTE_GROUP = 'attribute_group';
    const TABLE_ATTRIBUTE_GROUP_VALUE = 'attribute_group_value';
    const TABLE_ATTRIBUTE_SET_VALUE = 'attribute_set_value';
    const TABLE_ATTRIBUTE_OPTION = 'attribute_option';
    const TABLE_PRODUCT = 'product';
    const TABLE_PRODUCT_MEDIA = 'product_media';
    const TABLE_PRODUCT_CAT = 'product_category';
    const TABLE_PRODUCT_OPTION = 'product_option';
    const TABLE_PRODUCT_OPTION_VALUE = 'product_option_value';
    const TABLE_PRODUCT_RELATION = 'product_relation';
    const TABLE_PRODUCT_TEXT = 'product_text';
    const TABLE_PRODUCT_INT = 'product_int';
    const TABLE_PRODUCT_VARCHAR = 'product_varchar';
    const TABLE_PRODUCT_DATETIME = 'product_datetime';
    const TABLE_PRODUCT_DECIMAL = 'product_decimal';
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

    public function createAttributeTable(){
        $table_construct = array(
            'table' => self::TABLE_ATTRIBUTE,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'code' => 'VARCHAR(255) NOT NULL',
                'label' => 'VARCHAR(255)',
                'type' => 'VARCHAR(255) NOT NULL',
                'input' => 'VARCHAR(255) NOT NULL',
                'is_core' => 'TINYINT(2)',
                'is_show_grid' => 'TINYINT(2)',
                'is_required' => 'TINYINT(2)',
                'is_unique' => 'TINYINT(2)',
                'default_value' => 'TEXT',
            ),
            'unique' => array(
                array(
                    'code'
                ),
            ),

        );
        return $this->createTableQuery($table_construct,'createAttributeOptionTable',false);
    }
    public function createAttributeOptionTable(){
        $table_construct = array(
            'table' => self::TABLE_ATTRIBUTE_OPTION,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'value' => 'VARCHAR(255)',
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'attribute_id','value'
                ),
            ),

        );
        return $this->createTableQuery($table_construct,'createAttributeSetTable',false);
    }
    public function createAttributeSetTable(){
        $table_construct =array(
            'table' => self::TABLE_ATTRIBUTE_SET,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'code' => 'VARCHAR(255) NOT NULL',
                'label' => 'VARCHAR(255)',
            ),
            'unique' => array(
                array(
                    'code'
                ),
            ),

        );
        $result = $this->createTableQuery($table_construct,'createAttributeSetValueTable',false);
        if($result['result'] == 'success'){
            $add_data = $this->addAttributeSetDefault();
            if($add_data['result'] != 'success'){
                return $add_data;
            }
        }
//        print_r($result);exit;
        return $result;
    }

    public function addAttributeSetDefault(){
        $data = array(
            'code' => 'default',
            'label' => 'Default',
        );
        $truncate = $this->truncateTable(self::TABLE_ATTRIBUTE_SET);
        if(!$truncate || $truncate['result'] != 'success'){
            return $this->errorConnectDatabase($truncate['msg']);
        }
        $res = $this->insertTable(self::TABLE_ATTRIBUTE_SET,$data);
        if(!$res || $res['result'] != 'success'){
            return $this->errorConnectDatabase($res['msg']);
        }
        return array(
            'result' => 'success',
            'msg' => '',
        );
    }

    public function createAttributeGroupTable(){
        $table_construct =array(
            'table' => self::TABLE_ATTRIBUTE_GROUP,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'code' => 'VARCHAR(255) NOT NULL',
                'label' => 'VARCHAR(255)',
                'attribute_set_id' => 'SMALLINT(5) NOT NULL'
            ),
            'references' => array(
                'attribute_set_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE_SET,
                    'row' => 'id',
                )
            ),
        );
        return $this->createTableQuery($table_construct,'createAttributeSetValueTable',false);
    }
    public function createAttributeSetValueTable(){
        $table_construct =array(
            'table' => self::TABLE_ATTRIBUTE_SET_VALUE,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_set_id' => 'SMALLINT(5) NOT NULL',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
            ),
            'references' => array(
                'attribute_set_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE_SET,
                    'row' => 'id',
                ),
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'attribute_set_id','attribute_id'
                ),
            ),
        );
        $result = $this->createTableQuery($table_construct,'createProductTable',false);
        if($result['result'] == 'success'){
            $add_data = $this->addAttributeDefault();
            if($add_data['result'] != 'success'){
                return $add_data;
            }
        }
        return $result;
    }
    public function createProductTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_set' => 'SMALLINT(5) NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'sku' => 'VARCHAR(255) NOT NULL',
                'price' => 'decimal(12,4) NOT NULL',
                'status' => 'TINYINT(2)',
                'created_at' => 'DATETIME',
                'updated_at' => 'DATETIME',
                'description' => 'TEXT',
                'url_key' => 'VARCHAR(255) NOT NULL',
            ),
            'references' => array(
                'attribute_set' => array(
                    'table' =>self::TABLE_ATTRIBUTE_SET,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'sku'
                ),
                array(
                    'url_key'
                )
            ),
        );
        return $this->createTableQuery($table_construct,'createProductMediaTableConstruct',false);
    }

    public function createProductMediaTableConstruct(){
        require_once ('Core.php');
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_MEDIA,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'BIGINT NOT NULL',
                'media_id' => 'BIGINT NOT NULL',
                'status' => 'TINYINT(2)',
                'is_thumbnail' => 'TINYINT(2)'
            ),
            'references' => array(
                'media_id' => array(
                    'table' =>Setup_Install_Core::TABLE_MEDIA,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','media_id'
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createProductCategoryTable',false);

    }

    public function createProductCategoryTable(){
        require_once ('Category.php');

        $table_construct = array(
            'table' => self::TABLE_PRODUCT_CAT,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'BIGINT NOT NULL',
                'category_id' => 'BIGINT NOT NULL',
            ),
            'references' => array(
                'category_id' => array(
                    'table' =>Setup_Install_Category::TABLE_CATEGORY,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'category_id','product_id'
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createProductOptionTable',false);
    }
    public function createProductOptionTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_OPTION,
            'rows' => array(
                'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'BIGINT NOT NULL',
                'is_required' => 'TINYINT(2)',
                'type' => 'VARCHAR(255) NOT NULL',
                'title' => 'VARCHAR(255) NOT NULL'
            ),
            'references' => array(
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
        );
        return $this->createTableQuery($table_construct,'createProductOptionValueTable',false);
    }
    public function createProductOptionValueTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_OPTION_VALUE,
            'rows' => array(
                'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'option_id' => 'INT(10) NOT NULL',
                'is_default' => 'TINYINT(2)',
                'title' => 'VARCHAR(255) NOT NULL',
                'price' => 'DECIMAL(12,4)',
                'price_type' => 'VARCHAR(7)'
            ),
            'references' => array(
                'option_id' => array(
                    'table' =>self::TABLE_PRODUCT_OPTION,
                    'row' => 'id',
                )
            ),
        );
        return $this->createTableQuery($table_construct,'createProductRelationTable',false);
    }
    public function createProductRelationTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_RELATION,
            'rows' => array(
                'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'parent_id' => 'BIGINT NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
            ),
            'references' => array(
                'parent_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'parent_id','product_id'
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createProductIntTable',false);
    }

    public function createProductIntTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_INT,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
                'value' => 'INT(11)'
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','attribute_id'
                ),
            ),

        );
        return $this->createTableQuery($table_construct,'createProductVarcharTable',false);
    }
    public function createProductVarcharTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_VARCHAR,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
                'value' => 'VARCHAR(255)'
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','attribute_id'
                ),
            ),


        );
        return $this->createTableQuery($table_construct,'createProductTextTable',false);
    }
    public function createProductTextTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_TEXT,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
                'value' => 'TEXT'
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','attribute_id'
                ),
            ),


        );
        return $this->createTableQuery($table_construct,'createProductDatetimeTable',false);
    }
    public function createProductDatetimeTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_DATETIME,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
                'value' => 'datetime'
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','attribute_id'
                ),
            ),


        );
        return $this->createTableQuery($table_construct,'createProductDecimalTable',false);
    }
    public function createProductDecimalTable(){
        $table_construct = array(
            'table' => self::TABLE_PRODUCT_DECIMAL,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'attribute_id' => 'SMALLINT(5) NOT NULL',
                'product_id' => 'BIGINT NOT NULL',
                'value' => 'DECIMAL(12,4)	'
            ),
            'references' => array(
                'attribute_id' => array(
                    'table' =>self::TABLE_ATTRIBUTE,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>self::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
            'unique' => array(
                array(
                    'product_id','attribute_id'
                ),
            ),


        );
        return $this->createTableQuery($table_construct,'',true);
    }
    public function addAttributeDefault(){
        $truncate = $this->truncateTable(self::TABLE_ATTRIBUTE);
        if(!$truncate || $truncate['result'] != 'success'){
            return $this->errorConnectDatabase($truncate['msg']);
        }
        $truncate = $this->truncateTable(self::TABLE_ATTRIBUTE_SET_VALUE);
        if(!$truncate || $truncate['result'] != 'success'){

            return $this->errorConnectDatabase($truncate['msg']);
        }
        $file_attribute = _MODULE_DIR_.DS.'media'.DS.'attribute.csv';
        $attribute_data = $this->readCsv($file_attribute);
        if($attribute_data['result'] != 'success'){

            return $this->errorConnectDatabase($attribute_data['msg']);
        }
        $attribute_datas = $attribute_data['data'];
//        print_r($attribute_datas);exit;
        foreach ($attribute_datas as $data){
            $data = $this->syncCsvTitleRow($data['title'],$data['row']);
            $insert = $this->insertTable(self::TABLE_ATTRIBUTE,$data);
            if($insert['result'] != 'success'){
//                var_dump($insert);exit;

                return $this->errorConnectDatabase($insert['msg']);
            }
            $attribute_id = $insert['data'];
            $attribute_set_data = array(
                'attribute_set_id' => 1,
                'attribute_id' => $attribute_id,
            );
            $insert = $this->insertTable(self::TABLE_ATTRIBUTE_SET_VALUE,$attribute_set_data);
            if($insert['result'] != 'success'){

                return $this->errorConnectDatabase($insert['msg']);
            }
        }
        return array(
            'result' => 'success',
        );
    }
    protected function syncCsvTitleRow($csv_title, $csv_row) {
        if (!$csv_row) {
            return array();
        }
        $row_value = array_filter($csv_row);
        if (!$row_value || empty($row_value)) {
            return array();
        }
        $data = array();
        foreach ($csv_title as $key => $title_name) {
            $data[$title_name] = (isset($csv_row[$key])) ? $csv_row[$key] : null;
        }
        return $data;
    }
}