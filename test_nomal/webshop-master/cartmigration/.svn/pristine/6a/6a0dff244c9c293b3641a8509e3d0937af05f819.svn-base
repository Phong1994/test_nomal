<?php

class LECM_Db_Sqlite
{
    protected $_config;
    protected $_conn;
    protected $_dbPath;
    protected $_dbPrefix;
    const MSG_ERR = 'Could not connect database.';

    public function __construct($config = null)
    {
        if($config){
            $this->setConfig($config);
        }
    }

    /**
     * TODO: CONFIG
     */

    public function setConfig($config)
    {
        $config = array_merge($this->defaultConfig(), $config);
        $this->setDbPath($config['db_path'])
            ->setDbPrefix($config['db_prefix']);
        return $this;
    }

    public function setDbPath($dbPath)
    {
        $this->_dbPath = $dbPath;
        return $this;
    }

    public function getDbPath()
    {
        return $this->_dbPath;
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
            'db_path' => '',
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
            $this->_conn->close();
        }
        return $this;
    }

    public function refreshConnect()
    {
        $this->closeConnect();
        $this->_conn = $this->_createConnect();
        return $this->_conn;
    }

    public function getConnect()
    {
        if(!$this->_conn){
            $this->connect();
        }
        return $this->_conn;
    }

    protected function _createConnect()
    {
        $dbPath = $this->getDbPath();
        if(!$dbPath){
            return null;
        }
        $conn = new SQLite3($dbPath);
        if(!$conn){
            Bootstrap::log($conn->lastErrorMsg(), 'sqlite');
        }
        return $conn;
    }

    /**
     * TODO: QUERY
     */

    public function selectRaw($query)
    {
        try {
            $conn = $this->getConnect();
            if(!$conn){
                return array(
                    'result' => 'error',
                    'msg' => self::MSG_ERR,
                    'data' => null,
                );
            }
            $res = $conn->query($query);
            $rows = array();
            while($row = $res->fetchArray(SQLITE3_ASSOC) ){
                $rows[] = $row;
            }
			return array(
                'result' => 'success',
                'msg' => '',
                'data' => $rows
            );
            //return $rows;
        } catch(Exception $e){
            Bootstrap::log($e->getMessage(), 'sqlite');
            return array(
                'result' => 'error',
                'msg' => $e->getMessage(),
                'data' => null
            );
        }
    }

    public function insertRaw($query, $insert_id = false)
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
            $result = $conn->exec($query);
            if($insert_id){
                $result = $conn->lastInsertRowID();
            }
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result,
            );
        } catch(Exception $e) {
            Bootstrap::log($e->getMessage(), 'sqlite');
            return array(
                'result' => 'error',
                'msg' => $e->getMessage(),
                'data' => null
            );
        }
    }

    public function queryRaw($query)
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
            $result = $conn->exec($query);
            return array(
                'result' => 'success',
                'msg' => '',
                'data' => $result
            );
        } catch(Exception $e) {
            Bootstrap::log($e->getMessage(), 'db');
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
            return false;
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
            return false;
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
            return false;
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
            return false;
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
            return false;
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
        if(is_int($value)){
            return $value;
        }
        if($value == ''){
            return "''";
        }
        if(!$value){
            return $value;
        }
        $conn = $this->getConnect();
        if($conn){
            $value = $conn->escapeString($value);
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

//            print_r($array);exit;
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