<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01/02/2018
 * Time: 14:46
 */

class Setup_Install_Order extends Model
{
    const TABLE_ORDER = 'order';
    const TABLE_ORDER_ADDRESS = 'order_address';
    const TABLE_ORDER_ITEM = 'order';

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

    public function createOrderTable(){
        require_once ('Customer.php');
        $table_construct = array(
            'table' => self::TABLE_ORDER,
            'rows' => array(
                'id' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'status' => 'VARCHAR(32)',
                'customer_id' => 'BIGINT',
                'customer_first_name' => 'VARCHAR(255) NOT NULL',
                'customer_last_name' => 'VARCHAR(255) NOT NULL',
                'customer_telephone' => 'VARCHAR(255) NOT NULL',
                'customer_email' => 'VARCHAR(255) NOT NULL',
                'created_at' => 'DATETIME ',
                'updated_at' => 'DATETIME',
                'billing_address' => 'INT(10)',
                'shipping_address' => 'INT(10)',
                'total_price' => 'decimal(12,4)',
                'total_shipping' => 'decimal(12,4)',
                'total_discount' => 'decimal(12,4)',
                'discount_type' => 'VARCHAR(32)',
                'total_item' => 'SMALLINT(5)',
                'coupon_code' => 'VARCHAR(32)',
            ),
            'references' => array(
                'customer_id' => array(
                    'table' =>Setup_Install_Customer::TABLE_CUSTOMER,
                    'row' => 'id',
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createOrderAddressTable');

    }
    public function createOrderAddressTable(){
        $table_construct = array(
            'table' => self::TABLE_ORDER_ADDRESS,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'order_id' => 'BIGINT NOT NULL',
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
                'type' => 'VARCHAR(32)',
            ),
            'references' => array(
                'order_id' => array(
                    'table' =>self::TABLE_ORDER,
                    'row' => 'id',
                ),
            ),
        );
        return $this->createTableQuery($table_construct,'createOrderItemTable');

    }
    public function createOrderItemTable(){
        require_once ('Product.php');
        $table_construct = array(
            'table' => self::TABLE_ORDER_ITEM,
            'rows' => array(
                'id' => 'SMALLINT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'order_id' => 'BIGINT NOT NULL',
                'product_id' => 'BIGINT',
                'status' => 'SMALLINT(5)',
                'product_options' => 'text',
                'sku' => 'VARCHAR(255)',
                'name' => 'VARCHAR(255)',
                'price' => 'decimal(12,4) not null',
                'discount' => 'decimal(12,4)',
                'discount_type' => 'VARCHAR(5)',
                'discount_special' => 'decimal(12,4)',
                'discount_special_type' => 'VARCHAR(5)',
                'weight' => 'decimal(12,4)',
                'total_price' => 'decimal(12,4) not null'
            ),
            'references' => array(
                'order_id' => array(
                    'table' =>self::TABLE_ORDER,
                    'row' => 'id',
                ),
                'product_id' => array(
                    'table' =>Setup_Install_Product::TABLE_PRODUCT,
                    'row' => 'id',
                )
            ),
        );
        return $this->createTableQuery($table_construct,'',true);

    }

}