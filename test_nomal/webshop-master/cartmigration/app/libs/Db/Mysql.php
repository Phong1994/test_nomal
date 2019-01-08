<?php

class LECM_Db_Mysql
{
    protected $_config;
    protected $_conn;
    protected $_dbHost;
    protected $_dbUsername;
    protected $_dbPassword;
    protected $_dbName;
    protected $_dbPrefix;
    const MSG_ERR = 'Could not connect database.';

    public function __construct($config = null){
        if($config){
            $this->setConfig($config);
        }
    }

    /**
     * TODO: CONFIG
     */
    public function setConfig($config)
    {
        $config = array_merge($this->defaultConfig(), (array) $config);
        $this->setDbHost($config['db_host'])
            ->setDbUsername($config['db_username'])
            ->setDbPassword($config['db_password'])
            ->setDbName($config['db_name'])
            ->setDbPrefix($config['db_prefix']);
        return $this;
    }

    public function setDbHost($host = '')
    {
        $this->_dbHost = $host;
        return $this;
    }

    public function getDbHost()
    {
        $host = $this->_dbHost;
        if($host === null){
            $defaultConfig = $this->defaultConfig();
            $host = $defaultConfig['db_host'];
        }
        return $host;
    }

    public function setDbUsername($username = '')
    {
        $this->_dbUsername = $username;
        return $this;
    }

    public function getDbUsername()
    {
        $username = $this->_dbUsername;
        if($username === null){
            $defaultConfig = $this->defaultConfig();
            $username = $defaultConfig['db_username'];
        }
        return $username;
    }

    public function setDbPassword($password = '')
    {
        $this->_dbPassword = $password;
        return $this;
    }

    public function getDbPassword()
    {
        $password = $this->_dbPassword;
        if($password === null){
            $defaultConfig = $this->defaultConfig();
            $password = $defaultConfig['db_password'];
        }
        return $password;
    }

    public function setDbName($name = '')
    {
        $this->_dbName = $name;
        return $this;
    }

    public function getDbName()
    {
        $name = $this->_dbName;
        if($name === null){
            $defaultConfig = $this->defaultConfig();
            $name = $defaultConfig['db_name'];
        }
        return $name;
    }

    public function setDbPrefix($prefix = '')
    {
        $this->_dbPrefix = $prefix;
        return $this;
    }

    public function getDbPrefix()
    {
        $prefix = $this->_dbPrefix;
        if($prefix === null){
            $defaultConfig = $this->defaultConfig();
            $prefix = $defaultConfig['db_prefix'];
        }
        return $prefix;
    }

    public function defaultConfig()
    {
        return array(
            'db_host' => '',
            'db_username' => '',
            'db_password' => '',
            'db_name' => '',
            'db_prefix' => '',
        );
    }

    /**
     * TODO: CONNECT
     */
    public function connect()
    {
        return $this->refreshConnect();
    }

    public function closeConnect()
    {
        if($this->_conn){
            @mysql_close($this->_conn);
        }
        return true;
    }

    protected function _createConnect()
    {
        $db_host = $this->getDbHost() ;
        $db_username = $this->getDbUsername();
        $db_password = $this->getDbPassword();
        try {
            $connect = @mysql_connect($db_host, $db_username, $db_password);
            if(!$connect){
                Bootstrap::log('Could not connect to database: ' . $db_host . ' ' . $db_username . '/' . $db_password, 'mysql');
                return null;
            }
            $db_name = $this->getDbName();
            $db_exists = mysql_select_db($db_name, $connect);
            if(!$db_exists){
                Bootstrap::log('Database ' . $db_name . 'not exists.', 'mysql');
                return null;
            }
            $charset = Bootstrap::getConfig('db_charset');
            if($charset){
                mysql_set_charset($charset, $connect);
            }
            return $connect;
        } catch (Exception $e){
            Bootstrap::log($e->getMessage(), 'mysql');
            return null;
        }
    }

    public function refreshConnect()
    {
        $this->closeConnect();
        $this->_conn = $this->_createConnect();
        return $this->_conn;
    }

    public function getConnect()
    {
        if($this->_conn){
            $ping = mysql_ping($this->_conn);
            if(!$ping){
                $this->refreshConnect();
            }
        } else {
            $this->_conn = $this->_createConnect();
        }
        return $this->_conn;
    }

    /**
     * TODO: QUERY
     */
    public function selectRaw($query)
    {
        try{
            $conn = $this->getConnect();
            if(!$conn){
                return array(
                    'result' => 'error',
                    'msg' => self::MSG_ERR,
                    'data' => null
                );
            }
            $res = mysql_query($query, $conn);
            if(mysql_errno($conn)){
                Bootstrap::log(mysql_error($conn), 'mysql');
                return array(
                    'result' => 'error',
                    'msg' => mysql_error($conn),
                    'data' => null
                );
            }
            $result = array();
            while($row = mysql_fetch_array($res, MYSQL_ASSOC)){
                $result[] = $row;
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch (Exception $e){
            Bootstrap::log($e->getMessage(), 'mysql');
            return array(
                'result' => "error",
                'msg' => $e->getMessage(),
                'data' => null
            );
        }
    }

    public function insertRaw($query, $insert_id = false)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        try {
            $result = mysql_query($query, $conn);
            if(mysql_errno($conn)){
                Bootstrap::log(mysql_error($conn), 'mysql');
                return array(
                    'result' => 'error',
                    'msg' => mysql_error($conn),
                    'data' => false
                );
            }
            if($insert_id){
                $last_insert_id = mysql_insert_id($conn);
                return array(
                    'result' => 'success',
                    'msg' => '',
                    'data' => $last_insert_id,
                );
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch(Exception $e){
            Bootstrap::log($e->getMessage(), 'mysql');
            return array(
                'result' => 'success',
                'msg' => $e->getMessage(),
                'data' => false
            );
        }
    }

    public function queryRaw($query)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        try {
            $result = mysql_query($query, $conn);
            if(mysql_errno($conn)){
                Bootstrap::log(mysql_error($conn), 'mysql');
                return array(
                    'result' => 'error',
                    'msg' => mysql_error($conn),
                    'data' => false
                );
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch(Exception $e){
            Bootstrap::log($e->getMessage(), 'mysql');
            return array(
                'result' => 'error',
                'msg' => $e->getMessage(),
                'data' => false
            );
        }
    }

    public function selectObj($table, $where = null, $select_field = '*')
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        $table_name = $this->getTableName($table);
        $query = "SELECT " . $select_field . " FROM `" . $table_name . "`";
        if($where){
            if(is_string($where)){
                $query .= " WHERE " . $where;
            } else if(is_array($where)){
                $where_condition = $this->arrayToWhereCondition($where);
                $query .= " WHERE " . $where_condition;
            } else {

            }
        }
        return $this->selectRaw($query);
    }

    public function insertObj($table, $data, $insert_id = false)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        $table_name = $this->getTableName($table);
        $data_condition = $this->arrayToInsertCondition($data);
        if(!$data_condition){
            return false;
        }
        $query = "INSERT INTO `" . $table_name . "` " . $data_condition;
        return $this->insertRaw($query, $insert_id);
    }

    public function updateObj($table, $data, $where = null)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        $table_name = $this->getTableName($table);
        $set_condition = $this->arrayToSetCondition($data);
        if(!$set_condition){
            return false;
        }
        $query = "UPDATE `" . $table_name . "` SET " . $set_condition;
        if($where){
            if(is_string($where)){
                $query .= " WHERE " . $where;
            } else if(is_array($where)){
                $where_condition = $this->arrayToWhereCondition($where);
                $query .= " WHERE " . $where_condition;
            } else {

            }
        }
        return $this->queryRaw($query);
    }

    public function deleteObj($table, $where = null)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        $table_name = $this->getTableName($table);
        $query = "DELETE FROM `" . $table_name . "`";
        if($where){
            if(is_string($where)){
                $query .= " WHERE " . $where;
            } else if(is_array($where)){
                $where_condition = $this->arrayToWhereCondition($where);
                $query .= " WHERE " . $where_condition;
            } else {

            }
        }
        return $this->queryRaw($query);
    }

    public function truncateObj($table)
    {
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        $table_name = $this->getTableName($table);
        $query = "TRUNCATE TABLE `" . $table_name . "`";
        return $this->queryRaw($query);
    }

    public function escape($value)
    {
        if($value === null){
            return 'null';
        }
        if($value === ''){
            return "''";
        }
        if(!$value){
            return $value;
        }
        if(is_int($value)){
            return $value;
        }
        $conn = $this->getConnect();
        if($conn && function_exists("mysql_real_escape_string")){
            $value = mysql_real_escape_string($value, $conn);
        } else {
            $value = addslashes($value);
        }
        return "'" . $value . "'";
    }

    public function arrayToInCondition($array)
    {
        if(empty($array)){
            return "('null')";
        }
        $array = array_map(array($this, 'escape'), $array);
        $result = "(" . implode(",", $array) . ")";
        return $result;
    }

    public function arrayToSetCondition($array)
    {
        if(empty($array)){
            return '';
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`" . $key . "` = " . $this->escape($value);
        }
        $result = implode(',', $data);
        return $result;
    }

    public function arrayToInsertCondition($array, $allow_keys = null)
    {
        if(!$array){
            return false;
        }
        $keys = array_keys($array);
        $data_key = $data_value = array();
        if(!$allow_keys){
            $data_key = $keys;
            $data_value = array_values($array);
            foreach($data_value as $key => $value){
                $data_value[$key] = $this->escape($value);
            }
        } else {
            foreach($keys as $key){
                if(in_array($key, $allow_keys)){
                    $data_key[] = $key;
                    $value = $array[$key];
                    if(is_int($value)){
                        $data_value[] = $value;
                    } else {
                        $data_value[] = $this->escape($value);
                    }
                }
            }
        }
        if(!$data_key){
            return false;
        }
        $key_condition = '(`' . implode('`, `', $data_key) . '`)';
        $value_condition = "(" . implode(", ", $data_value) . ")";
        $condition = $key_condition . " VALUES " . $value_condition;
        return $condition;
    }

    public function arrayToWhereCondition($array)
    {
        if(!$array){
            return '1 = 1';
        }
        if(is_string($array)){
            return $array;
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`" . $key . "` = " . $this->escape($value) . "";
        }
        $condition = implode(" AND ", $data);
        return $condition;
    }

    public function arrayToCreateTableSql($array)
    {
        if(!$array){
            return array(
                'result' => 'error',
                'msg' => "Data not exists."
            );
        }
        $table = $array['table'];
        $rowData = $array['rows'];
        if(!$table || !$rowData){
            return array(
                'result' => 'error',
                'msg' => 'Table data not exists'
            );
        }
        $rows = array();
        foreach($rowData as $row_name => $row_data){
            $row = "`{$row_name}` {$row_data}";
            $rows[] = $row;
        }
        $table_name = $this->getTableName($table);
        $query = "CREATE TABLE IF NOT EXISTS {$table_name} (";
        $query .= implode(',', $rows);
        $query .= ")";
        if(isset($array['meta'])){
            $query .= " " . $array['meta'];
        }
        return array(
            'result' => 'success',
            'query' => $query
        );
    }

    public function getTableName($table)
    {
        $table_name = $this->_dbPrefix . $table;
        return $table_name;
    }
}
