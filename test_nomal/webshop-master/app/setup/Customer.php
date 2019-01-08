<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:46
 */

class Setup_Install_Customer extends Model
{
    const TABLE_CUSTOMER_ADDRESS = 'customer_address';
    const TABLE_CUSTOMER = 'customer';
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

    public function createCustomerTable(){
        require_once ('Core.php');
        $table_construct = array(
            'table' => self::TABLE_CUSTOMER,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'email' => 'VARCHAR(255) NOT NULL',
                'group_id' => 'SMALLINT(5)',
                'created_at' => 'DATETIME ',
                'updated_at' => 'DATETIME',
                'status' => 'SMALLINT(5) NOT NULL',
                'first_name' => 'VARCHAR(255) NOT NULL',
                'last_name' => 'VARCHAR(255) NOT NULL',
                'telephone' => 'VARCHAR(255) NOT NULL',
                'password' => 'TEXT NOT NULL',
                'token' => 'TEXT',
                'token_created_at' => 'DATETIME',
                'default_billing' => 'INT(10)',
                'default_shipping' => 'INT(10)',
                'gender' => 'INT(2)',
                'dob' => 'DATETIME',
            ),
            'references' => array(
                'group_id' => array(
                    'table' =>Setup_Install_Core::TABLE_CUSTOMER_GROUP,
                    'row' => 'id',
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createCustomerAddressTable');

    }
    public function createCustomerAddressTable(){
        $table_construct = array(
            'table' => self::TABLE_CUSTOMER_ADDRESS,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'customer_id' => 'BIGINT NOT NULL',
                'value' => 'VARCHAR(255)',
                'created_at' => 'DATETIME',
                'updated_at' => 'DATETIME',
                'status' => 'SMALLINT(5)',
                'first_name' => 'VARCHAR(255)',
                'last_name' => 'VARCHAR(255)',
                'telephone' => 'VARCHAR(255)',
                'email' => 'VARCHAR(255)',
                'city' => 'VARCHAR(255)',
                'street' => 'VARCHAR(255)',
            ),
            'references' => array(
                'customer_id' => array(
                    'table' =>self::TABLE_CUSTOMER,
                    'row' => 'id',
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'',true);

    }
}