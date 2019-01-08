<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09/02/2018
 * Time: 16:31
 */
class Model implements Abstract_Model {
    protected $_db;
    protected $_main_table;
    protected $_data;
    protected $_limit;
    protected $_page;
    protected $_sort;
    protected $_select_field;
    protected $_data_filter = array();
    protected $table_construct = array();
    public function __construct()
    {
        $this->_main_table = 'setup_database';
        if (!$this->_db) {
            $dbConfig = Bootstrap::getDbConfig();
            $db       = Libs_Db::getInstance($dbConfig);
            if (!$db) {
                return null;
            }
            $connect = $db->getConnect();
            if ($connect) {
                $this->_db = $db;
            }
        }
        $this->_db->queryRaw('SET FOREIGN_KEY_CHECKS=0;');
    }

    public function getDb()
    {
        if (!$this->_db) {
            $dbConfig = Bootstrap::getDbConfig();
            $db       = Libs_Db::getInstance($dbConfig);
            if (!$db) {
                return null;
            }
            $connect = $db->getConnect();
            if ($connect) {
                $this->_db = $db;
            }
        }
        $this->_db->queryRaw('SET FOREIGN_KEY_CHECKS=0;');
        return $this->_db;
    }
    /**
     * TODO: DATABASE
     */
    public function countTable($table){
        return $this->_db->countTable($table);
    }
    public function selectPage($table,$where = null,$select_field = '*',$limit = '',$pages = 1,$order_by = 'id'){
        $page_data = $this->_db->selectPage($table,$where ,$select_field ,$limit,$pages,$order_by);
        if($page_data['result'] == 'success'){
            return $page_data['data'];
        }
        return array();
    }
    public function selectTable($table, $where = null)
    {
        return $this->_db->selectObj($table, $where);
    }

    public function selectTableRow($table, $where = null)
    {
        $rows = $this->selectTable($table, $where);
        if (!$rows || $rows['result'] != 'success') {
            return false;
        }

        return isset($rows['data'][0]) ? $rows['data'][0] : false;
    }

    public function insertTable($table, $data, $insert_id = true)
    {
        return $this->_db->insertObj($table, $data, $insert_id);

    }

    public function resultConstruct(){
        return array(
            'result' => '',
            'msg' => '',
            'data' => '',
        );
    }

    public function updateTable($table, $data, $where = null)
    {
        return  $this->_db->updateObj($table, $data , $where);
    }

    public function deleteTable($table, $where = null)
    {
        return $this->_db->deleteObj($table, $where);
    }

    public function escape($value)
    {
        return $this->_db->escape($value);
    }

    public function arrayToInCondition($array)
    {
        return $this->_db->arrayToInCondition($array);
    }

    public function arrayToSetCondition($array)
    {
        return $this->_db->arrayToSetCondition($array);
    }

    public function arrayToInsertCondition($array, $allow_keys = null)
    {
        return $this->_db->arrayToInsertCondition($array, $allow_keys);
    }

    public function arrayToWhereCondition($array)
    {
        return $this->_db->arrayToWhereCondition($array);
    }

    public function arrayToCreateTableSql($array)
    {
        return $this->_db->arrayToCreateTableSql($array);
    }
    public function queryRaw($query){
        return $this->_db->queryRaw($query);
    }

    public function truncateTable($query){
        return $this->_db->truncateTable($query);

    }

    function getVersionInstall($type)
    {
        $version = $this->selectTableRow('setup_database',array('type' => $type));
        if($version){
            return $version['version'];
        }
        return '0.0.0';
    }
    function setVersionInstall($type,$version = '1.0.0')
    {
        $version = array(
            'type' => $type,
            'version' => $version,
        );
        return $this->insertTable('setup_database',$version);
    }
    public function getConfig($key,$default = ''){
        return Bootstrap::getConfig($key,$default);
    }
    public function setConfig($key,$data){
        return Bootstrap::setConfig($key,$data);
    }
    public function unsetConfig($key){
        return Bootstrap::unsetConfig($key);
    }
    public function errorConnectDatabase($msg = MSG_ERROR){
        return array(
            'result' => 'error',
            'msg' => $this->consoleError($msg),
            'data' => array(),
        );
    }
    public function responseSuccess($msg = '',$data = ''){
        return array(
            'result' => 'success',
            'msg' => $msg,
            'data' => $data
        );
    }
    public function getTableName($table){
        $prefix = Bootstrap::getConfig('db_prefix','');
        return $prefix.$table;
    }
    /**
     * Add class success to text for show in console
     */
    public function consoleSuccess($msg){
        $result = '<p class="console-success"> - ' . $msg . '</p>';
        return $result;
    }

    /**
     * Add class warning to text for show in console
     */
    public function consoleWarning($msg){
        $result = '<p class="console-warning"> - ' . $msg . '</p>';
        return $result;
    }

    /**
     * Add class error to text for show in console
     */
    public function consoleError($msg){
        $result = '<p class="console-error"> - ' . $msg . '</p>';
        return $result;
    }

    protected function defaultResponse()
    {
        return array(
            'result'    => '',
            'msg'       => '',
            'data'      => '',
        );
    }

    function createTableQuery($table_construct,$next_function,$finish = false){
        $query = $this->arrayToCreateTableSql($table_construct);
        if($query['result'] != 'success' || !isset($query['query'])){
            return $this->errorConnectDatabase();
        }
        $create = $this->queryRaw($query['query']);

        if($create['result'] == 'success'){
            return array(
                'result' => 'success',
                'msg' => $this->consoleSuccess('create table '.$this->getTableName($table_construct['table']).' success'),
                'data' => array(
                    'status' => $finish?'success':'process',
                    'function' => $next_function,
                ),
            );
        }
        return $this->errorConnectDatabase($create['msg']);
    }
    protected function readCsv($file_path) {
        if (!is_file($file_path)) {
            return array(
                'result' => 'error',
                'msg' => 'Path not exists'
            );
        }
        try {
            $finish = false;
            $count = 0;
            $csv = fopen($file_path, 'r');
            $csv_title = "";
            $data = array();
            while (!feof($csv)) {
                $line = fgetcsv($csv);
                if($line){
                    if ($count == 0) {
                        $csv_title = $line;
                    }
                    else {
                        $data[] = array(
                            'title' => str_replace(' ', '_', $csv_title),
                            'row' => $line
                        );
                    }
                    $count++;
                }

            }
            fclose($csv);
            return array(
                'result' => 'success',
                'data' => $data,
                'finish' => $finish
            );
        } catch (Exception $e) {
            return array(
                'result' => 'error',
                'msg' => $e->getMessage()
            );
        }
    }
    public function getNewDate($time = null){
        if($time){
            return date("Y-m-d H:i:s",$time);
        }
        return date("Y-m-d H:i:s");
    }
    public function passwordHash($password){
        return md5($password);
    }
    public function getData($key = '',$default = ''){
        if(!$key){
            return $this->_data;
        }
        if(isset($this->_data[$key])){
            return $this->_data[$key];
        }
        return $default;
    }
    public function getValue($data, $key, $default = null)
    {
        if (!isset($data[$key])) {
            return $default;
        }
        return $data[$key];
    }
    public function filter()
    {
        // TODO: Implement filter() method.
        $limit = $this->_limit?$this->_limit:'';
        $page = $this->_page?$this->_page:1;
        $select_field = $this->_select_field?$this->_select_field:'*';
        $sort = $this->_sort?$this->_sort:'id';
        $filter = $this->selectPage($this->_main_table,$this->_data_filter,$select_field,$limit,$page,$sort);
        $this->afterFilter();
        if($filter){
            return $limit==1?$filter[0]:$filter;
        }
        return false;
    }

    public function afterFilter(){
        $this->_data_filter = array();
        $this->_sort = null;
        $this->_page = null;
        $this->_limit = null;
        $this->_select_field = null;
    }

    public function addFieldToFilter($key,$value)
    {
        // TODO: Implement addFieldToFilter() method.
        $this->_data_filter[$key] = $value;
        return $this;
    }
    public function getId(){
        return $this->getData('id');
    }
    public function addDataFilter($data)
    {
        // TODO: Implement addDataFilter() method.
        $this->_data_filter = $data;
        return $this;
    }

    public function setData($data)
    {
        // TODO: Implement addData() method.
        $this->_data = $data;
    }
    public function save()
    {
        // TODO: Implement save() method.
        $this->_data = $this->beforeSave();
        $insert = $this->insertTable($this->_main_table,$this->_data);
        if($insert['result'] != 'success'){
            $this->throwException($insert['msg']);
        }
        $id = $insert['data'];
        $this->addData('id',$id);
        return $id;
    }
    public function addData($key, $value)
    {
        // TODO: Implement addData() method.
        $this->_data[$key] = $value;
    }
    public function throwException($message){
        throw new Exception($message);
    }

    public function load($id)
    {
        // TODO: Implement load() method.
        $filter = $this->selectTableRow($this->_main_table,array('id' => $id));
        if($filter){
            $this->setData($filter);
            return $this;
        }
        return false;
    }

    public function beforeSave()
    {
        // TODO: Implement syncDataConstruct() method.
        return $this->_data;
    }
    public function getDataUpdate(){
        $data = $this->getData();
        if(isset($data['id'])){
            unset($data['id']);
        }
        return $data;
    }
    public function setLimit($limit){
        $this->_limit = $limit;
        return $this;
    }
    public function setPageFilter($page){
        $this->_page = $page;
        return $this;
    }
    public function setSelectField($select_field){
        $this->_select_field = $select_field;
        return $this;
    }

    public function setSort($sort){
        $this->_sort = $sort;
        return $this;
    }
    public function getTotalNumberPage(){
        if(!$this->_main_table){
            return 0;
        }
        if(!$this->_limit){
            return 1;
        }
        $count = $this->countTable($this->_main_table);
        return ceil($count/$this->_limit);
    }

}