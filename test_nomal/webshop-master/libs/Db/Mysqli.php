<?php

class Libs_Db_Mysqli
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
            mysqli_close($this->_conn);
        }
        return true;
    }

    protected function _createConnect()
    {
        $db_host = $this->getDbHost() ;
        $db_username = $this->getDbUsername();
        $db_password = $this->getDbPassword();
        $db_name = $this->getDbName();
        try{
            $connect = @mysqli_connect($db_host, $db_username, $db_password, $db_name);
            if(!$connect){
                Bootstrap::log('Could not connect to database: ' . $db_host . ' ' . $db_username . '/' . $db_password . ' ' . $db_name, 'mysqli');
                return null;
            }
            mysqli_set_charset($connect, 'utf8');
            return $connect;
        } catch(Exception $e){
            Bootstrap::log($e->getMessage(), 'mysqli');
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
            $ping = mysqli_ping($this->_conn);
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


    public function countTable($table){
        $query = "SELECT COUNT(1) FROM `{$this->getTableName($table)}`";
        $count = $this->selectRaw($query);
        if(isset($count['data'][0]['COUNT(1)'])){
            return $count['data'][0]['COUNT(1)'];
        }
        return 0;
    }
    public function selectRaw($query)
    {
        try {
            $conn = $this->getConnect();
            if(!$conn){
                return array(
                    'result' => 'error',
                    'msg' => self::MSG_ERR,
                    'data' => null
                );
            }
            $res = mysqli_query($conn, $query);
            if(mysqli_errno($conn)){
                Bootstrap::log(mysqli_error($conn), 'mysqli');
                return array(
                    'result' => 'error',
                    'msg' => mysqli_error($conn),
                    'data' => null
                );
            }
            $result = array();
            while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)){
                $result[] = $row;
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch (Exception $e){
            Bootstrap::log($query.'-'.$e->getMessage(), 'mysqli');
            return array(
                'result' => 'error',
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
//            print_r($query);exit;
            $result = mysqli_query($conn, $query);
            if(mysqli_errno($conn)){
                Bootstrap::log($query.'-'.mysqli_error($conn), 'mysqli');
                return array(
                    'result' => 'error',
                    'msg' => mysqli_error($conn),
                    'data' => false
                );
            }
            if($insert_id){
                $last_insert_id = mysqli_insert_id($conn);
                return array(
                    'result' => 'success',
                    'msg' => '',
                    'data' => $last_insert_id
                );
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch(Exception $e){
            Bootstrap::log($query.'-'.$e->getMessage(), 'mysqli');
            return array(
                'result' => 'error',
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
            $result = mysqli_query($conn, $query);
            if(mysqli_errno($conn)){
                Bootstrap::log(mysqli_error($conn), 'mysqli');
                return array(
                    'result' => 'error',
                    'msg' => mysqli_error($conn),
                    'data' => false
                );
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch(Exception $e){
            Bootstrap::log($query.'-'.$e->getMessage(), 'mysqli');
            return array(
                'result' => 'error',
                'msg' => $e->getMessage(),
                'data' => false
            );
        }
    }

    public function selectPage($table,$where = null,$select_field = '*',$limit = '',$page = 1,$order_by = 'id'){
        $conn = $this->getConnect();
        if(!$conn){
            return array(
                'result' => 'error',
                'msg' => self::MSG_ERR,
                'data' => null
            );
        }
        if(is_array($select_field)){
            $select_field = implode(',',$select_field);
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
        if($limit){
            $query .= " ORDER BY `{$order_by}` LIMIT {$limit} ";
            $offset = ($page - 1)*$limit;
            if($offset > 0){
                $query .= "OFFSET {$offset}";
            }
        }
        return $this->selectRaw($query);
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
        if(is_array($select_field)){
            $select_field = implode(',',$select_field);
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
            array(
                'result' => 'error',
                'msg' => 'no condition',
                'data' => null
            );
        }
        $query = "INSERT INTO `" . $table_name . "` " . $data_condition;
       return $this->insertRaw($query, $insert_id) ;
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

    public function truncateTable($table){
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
        if($conn && function_exists("mysqli_real_escape_string")){
            $value = mysqli_real_escape_string($conn, $value);
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
        $referenceData = isset($array['references'])?$array['references']:array();
        $uniqueData = isset($array['unique'])?$array['unique']:array();
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
        $references = array();
        foreach ($referenceData as $row_reference => $data_reference){
            $references[] = "FOREIGN KEY (".$row_reference.") REFERENCES ".$this->getTableName($data_reference['table'])."(".$data_reference['row'].")";
        }
        $unique = array();
        foreach ($uniqueData as $data){
            $name = '';
            $fields = array();
            foreach ($data as $field){
                $name .= $name?'-'.strtoupper($field):strtoupper($field);

                $fields[] = "`{$field}`";
            }
            $str_unique = "UNIQUE `{$name}` ( ";
            $str_unique .= implode(',',$fields).')';
            $unique[] = $str_unique;
        }
        $table_name = $this->getTableName($table);
        $query = "CREATE TABLE IF NOT EXISTS {$table_name} (";
        $query .= implode(',', $rows);
        if(count($references)){
            $query .= ",";
        }
        $query .= implode(',', $references);
        if(count($unique)){
            $query .= ",";
        }
        $query .= implode(',', $unique);

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