<?php

define('LECM_TOKEN', '123456');

class LECM_Connector
{
    var $dev_mode = false;
    var $action = null;
    var $adapter = null;
    var $response = null;

    function getResponse()
    {
        if(!$this->response){
            $this->response = new LECM_Connector_Response();
        }
        return $this->response;
    }

    function getAdapter()
    {
        if(!$this->adapter){
            $this->adapter = new LECM_Connector_Adapter();
        }
        return $this->adapter;
    }

    function getAction()
    {
        if(!$this->action){
            $this->action = new LECM_Connector_Action();
        }
        return $this->action;
    }

    function run()
    {
        if(empty($_GET)){
            echo "Connector Installed";
            if($this->dev_mode){
                $this->devModeCheck();
            }
            return ;
        }

        if (!$this->checkToken()) {
            $response = $this->getResponse();
            $response->error('Token is false !', null);
            return;
        }

        $action = $this->getAction();
        $action->setConnector($this);
        $action->run();
        return;
    }

    function checkToken() {
        if (isset($_GET['token']) && $_GET['token'] == LECM_TOKEN) {
            return true;
        } else {
            return false;
        }
    }

    function log($msg, $log_type = 'exception')
    {
        $log_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $log_type . '.log';
        if(is_array($msg)){
            $msg = serialize($msg);
        }
        $msg .= "\r\n";
        $date_time = date('Y-m-d H:i:s');
        @file_put_contents($log_file, $date_time . ' : ' . $msg, FILE_APPEND);
    }

    function devModeCheck()
    {
        echo "<br />";
        echo "Developer mode is enabled. Status check: <br />";

        $connector_folder = dirname(__FILE__);
        echo "Connector folder is writable ..." ;
        if(is_writable($connector_folder)){
            echo " ok";
        } else {
            echo "failed";
        }
        echo "<br />";
    }
}

class LECM_Connector_Response
{
    function createResponse($result, $msg, $obj) {
        $response = array();
        $response['result'] = $result;
        $response['msg'] = $msg;
        $response['object'] = $obj;
        echo base64_encode(serialize($response));
        return;
    }

    function error($msg = null, $obj = null) {
        $this->createResponse('error', $msg, $obj);
    }

    function success($msg = null, $obj = null) {
        $this->createResponse('success', $msg, $obj);
    }
}

class LECM_Connector_Action
{
    var $type = null;
    var $connector = null;

    function setConnector($connector) {
        $this->connector = $connector;
    }

    function run() {
        if (isset($_GET['action']) && $action = $this->getActionType($_GET['action'])) {
            $action->setConnector($this->connector);
            $action->run();
        } else {
            $response = $this->connector->getResponse();
            $response->createResponse('error', 'Action not found!', null);
            return;
        }
    }

    function getActionType($action_type) {
        $action = null;
        $action_type = strtolower($action_type);
        $class_name = __CLASS__ . '_' . ucfirst($action_type);
        if (class_exists($class_name)) {
            $action = new $class_name();
        }
        return $action;
    }

    function getResponse() {
        return $this->connector->getResponse();
    }

    function getAdapter() {
        return $this->connector->getAdapter();
    }

    function getCart($check = false) {
        $adapter = $this->getAdapter();
        $cart = $adapter->getCart($check);
        return $cart;
    }

    function getParams($key, $params, $default = null)
    {
        return isset($params[$key]) ? $params[$key] : $default;
    }

    function getRealPath($path)
    {
        $path = ltrim($path, '/');
        $full_path = LECM_STORE_BASE_DIR . $path;
        return $full_path;
    }

    function createParentDir($path, $mode = 0777)
    {
        $result = true;
        if (!is_dir(dirname($path))) {
            $result = @mkdir(dirname($path), 0777, true);
        }
        return $result;
    }

    function createFileSuffix($file_path, $suffix, $character = '_'){
        $new_path = '';
        $dir_name = pathinfo($file_path, PATHINFO_DIRNAME);
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        $file_ext = pathinfo($file_path , PATHINFO_EXTENSION);
        if($dir_name && $dir_name != '.') $new_path .= $dir_name.'/';
        $new_path .= $file_name.$character.$suffix.'.'.$file_ext;
        return $new_path;
    }
}

class LECM_Connector_Action_Check extends LECM_Connector_Action
{
    function run() {
        $response = $this->getResponse();
        $adapter = $this->getAdapter();
        $cart = $this->getCart(true);
        $obj['cms'] = $adapter->detectCartType();
        if ($cart) {
            $obj['image_category'] = $cart->imageDirCategory;
            $obj['image_product'] = $cart->imageDirProduct;
            $obj['image_manufacturer'] = $cart->imageDirManufacturer;
            $obj['table_prefix'] = $cart->tablePrefix;
            $obj['version'] = $cart->version;
            $obj['charset'] = $cart->charset;
            $obj['cookie_key'] = $cart->cookie_key;
            $obj['extend'] = $cart->extend;
            $dbConnect = LECM_Db::getInstance($cart);
            if ($dbConnect->getError()) {
                $obj['connect'] = array(
                    'result' => 'error',
                    'msg' => 'Not connect to database! Error: ' . $dbConnect->getError()
                );
            } else {
                $obj['connect'] = array(
                    'result' => 'success',
                    'msg' => 'Successful connect to database!'
                );
            }
        }

        $response->success('Successful check CMS!', $obj);
        return;
    }

}

class LECM_Connector_Action_Directory extends LECM_Connector_Action
{
    function run()
    {
        $data = array();
        $response = $this->getResponse();
        if(isset($_REQUEST['folders'])){
            $folders = unserialize(base64_decode($_REQUEST['folders']));
            foreach($folders as $key => $folder){
                $params = isset($folder['params']) ? $folder['params'] : array();
                $data[$key] = $this->getDir($folder['type'], $folder['folder'], $params);
            }
        }
        return $response->success(null, $data);
    }

    function getDir($type, $folder, $params = array())
    {
        $result = false;
        switch($type){
            case 'writable';
                $result = $this->writable($folder, $params);
                break;
            case 'exists';
                $result = $this->exists($folder, $params);
                break;
            case 'dir';
                $result = $this->dir($folder, $params);
                break;
            case 'tree';
                $result = $this->tree($folder, $params);
                break;
            case 'delete';
                $result = $this->delete($folder, $params);
                break;
            case 'create';
                $result = $this->create($folder, $params);
                break;
            default:
                break;
        }
        return $result;
    }

    function writable($folder, $params = array())
    {
        $result = false;
        if(!$this->exists($folder)){
            return $result;
        }
        $path = $this->getRealPath($folder);
        $result = is_writable($path);
        return $result;
    }

    function exists($folder, $params = array())
    {
        $path = $this->getRealPath($folder);
        return is_dir($path);
    }

    function dir($folder, $params = array())
    {
        $result = array();
        if(!$this->exists($folder)){
            return $result;
        }
        $path = $this->getRealPath($folder);
        $result = $this->readDir($path, false);
        return $result;
    }

    function tree($folder, $params = array())
    {
        $result = array();
        if(!$this->exists($folder)){
            return $result;
        }
        $path = $this->getRealPath($folder);
        $result = $this->readDir($path, true);
        return $result;
    }

    function delete($folder, $params = array())
    {
        $result = false;
        if(!$this->exists($folder)){
            return true;
        }
        if(!$this->writable($folder)){
            return $result;
        }
        $path = $this->getRealPath($folder);
        $self = $this->getParams('self', $params);
        $result = $this->deleteDir($path, $self);
        return $result;
    }

    function create($folder, $params= array())
    {
        $result = true;
        if($this->exists($folder)){
            return $result;
        }
        $path = $this->getRealPath($folder);
        $result = @mkdir(dirname($path), 0777, true);
        return $result;
    }

    function deleteDir($path, $self = true)
    {
        $path = rtrim($path, '/\\');
        $items = glob($path . '/*', GLOB_MARK);
        foreach ($items as $item) {
            if (is_dir($item)) {
                $this->deleteDir($item, true);
            } else {
                @unlink($item);
            }
        }
        if($self){
            @rmdir($path);
        }
        return true;
    }

    function readDir($path, $content = false)
    {
        $result = array();
        $path = rtrim($path, '/\\');
        $items = glob($path . '/*', GLOB_MARK);
        foreach ($items as $item) {
            if (is_dir($item)) {
                $folder_data = array(
                    'type' => 'folder',
                    'path' => basename($item),
                );
                if($content){
                    $folder_data['content'] = $this->readDir($item, true);
                }
                $result[] = $folder_data;
            } else {
                $result[] = array(
                    'type' => 'file',
                    'path' => basename($item)
                );
            }
        }
        return $result;
    }
}

class LECM_Connector_Action_File extends LECM_Connector_Action
{
    function run()
    {
        $data = array();
        $response = $this->getResponse();
        if(isset($_REQUEST['files'])){
            $files = unserialize(base64_decode($_REQUEST['files']));
            foreach($files as $key => $file){
                $params = isset($file['params']) ? $file['params'] : array();
                $data[$key] = $this->processFile($file['type'], $file['path'], $params);
            }
        }
        return $response->success(null, $data);
    }

    function processFile($type, $path, $params = array())
    {
        $result = false;
        switch($type){
            case 'download':
                $result = $this->download($path, $params);
                break;
            case 'exists':
                $result = $this->exists($path, $params);
                break;
            case 'rename':
                $result = $this->rename($path, $params);
                break;
            case 'delete':
                $result = $this->delete($path, $params);
                break;
            case 'content':
                $result = $this->content($path, $params);
                break;
            case 'copy':
                $result = $this->copy($path, $params);
                break;
            case 'move':
                $result = $this->move($path, $params);
                break;
            default:
                break;
        }
        return $result;
    }

    function download($path, $params = array(), $time = 10)
    {
        $result = false;
        if(!$time){
            return $result;
        }
        $override = $this->getParams('override', $params);
        $rename = $this->getParams('rename', $params);
        $url = $this->getParams('url', $params);
        if(!$url){
            return $result;
        }
        if($this->exists($path)){
            if($rename){
                $path = $this->rename($path);
            } else {
                if(!$override){
                    return $result;
                }
                $delete_file = $this->delete($path);
                if(!$delete_file){
                    return $result;
                }
            }
        }
        $full_path = $this->getRealPath($path);
        $this->createParentDir($full_path);
        $fp = @fopen($full_path, 'wb');
        if(!$fp){
            return $result;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        $connector = new LECM_Connector();
        if(curl_errno($ch) && $connector->dev_mode){
            LECM_Connector::log(curl_error($ch), 'curl');
        }
        curl_close($ch);
        @flush($fp);
        @fclose($fp);
        if($data){
            $result = $path;
        }
        if(!$result){
            $time--;
            $result = $this->download($path, $params, $time);
        }
        return $result;
    }

    function exists($path, $params = array())
    {
        $full_path = $this->getRealPath($path);
        return file_exists($full_path);
    }

    function rename($path, $params = array())
    {
        $path = ltrim($path, '/');
        $new_path = $path;
        $full_path = $this->getRealPath($new_path);
        $i = 1;
        while(file_exists($full_path)){
            $new_path = $this->createFileSuffix($path, $i);
            $full_path = $this->getRealPath($new_path);
            $i++;
        }
        return $new_path;
    }

    function delete($path, $params = array())
    {
        $result = true;
        if(!$this->exists($path)){
            return $result;
        }
        $full_path = $this->getRealPath($path);
        $result = @unlink($full_path);
        return $result;
    }

    function content($path, $params = array())
    {
        $result = '';
        $full_path = $this->getRealPath($path);
        if(!$this->exists($path)){
            return $result;
        }
        $result = @file_get_contents($full_path);
        return $result;
    }

    function copy($path, $params = array())
    {
        $result = false;
        $override = $this->getParams('override', $params);
        $copy_path = $this->getParams('copy', $params);
        if(!$copy_path){
            return $result;
        }
        if(!$this->exists($path)){
            return $result;
        }
        if($this->exists($copy_path)){
            if(!$override){
                return $result;
            }
            $delete_file = $this->delete($copy_path);
            if(!$delete_file){
                return $result;
            }
        }
        $full_path = $this->getRealPath($path);
        $full_copy_path = $this->getRealPath($copy_path);
        $this->createParentDir($full_copy_path);
        $result = @copy($full_path, $full_copy_path);
        return $result;
    }

    function move($path, $params = array())
    {
        $result = false;
        $override = $this->getParams('override', $params);
        $move_path = $this->getParams('move', $params);
        if(!$move_path){
            return $result;
        }
        if(!$this->exists($path)){
            return $result;
        }
        if($this->exists($move_path)){
            if(!$override){
                return $result;
            }
            $delete_file = $this->delete($move_path);
            if(!$delete_file){
                return $result;
            }
        }
        $full_path = $this->getRealPath($path);
        $full_move_path = $this->getRealPath($move_path);
        $this->createParentDir($full_move_path);
        $result = rename($full_path, $full_move_path);
        return $result;
    }

}

class LECM_Connector_Action_Image extends LECM_Connector_Action
{
    var $_file = null;

    function run()
    {
        $data = array();
        $response = $this->getResponse();
        if(isset($_REQUEST['images'])){
            $images = unserialize(base64_decode($_REQUEST['images']));
            foreach($images as $key => $image){
                $params = isset($image['params']) ? $image['params'] : array();
                $data[$key] = $this->processImage($image['type'], $image['path'], $params);
            }
        }
        return $response->success(null, $data);
    }

    function processImage($type, $path, $params = array())
    {
        $result = false;
        switch($type){
            case 'download':
                $result = $this->download($path, $params);
                break;
            case 'exists':
                $result = $this->exists($path, $params);
                break;
            case 'rename':
                $result = $this->rename($path, $params);
                break;
            case 'delete':
                $result = $this->delete($path, $params);
                break;
            case 'copy':
                $result = $this->copy($path, $params);
                break;
            case 'move':
                $result = $this->move($path, $params);
                break;
            case 'resize':
                $result = $this->resize($path, $params);
                break;
            default:
                break;
        }
        return $result;
    }

    function download($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->download($path, $params);
        return $result;
    }

    function exists($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->exists($path, $params);
        return $result;
    }

    function rename($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->rename($path, $params);
        return $result;
    }

    function delete($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->delete($path, $params);
        return $result;
    }

    function copy($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->copy($path, $params);
        return $result;
    }

    function move($path, $params = array())
    {
        $file = $this->getActionFile();
        $result = $file->move($path, $params);
        return $result;
    }

    function resize($path, $params = array())
    {
        $desc_path = $this->getParams('desc', $params);
        $width = $this->getParams('width', $params);
        $height = $this->getParams('height', $params);
        $crop = $this->getParams('crop', $params);
        $proportional = $this->getParams('proportional', $params);
        $quality = $this->getParams('quality', $params);
        if(!$quality){
            $quality = 100;
        }
        $result = $this->resizeImage($path, $desc_path, $width, $height, $crop, $proportional, $quality);
        return $result;
    }

    function getActionFile()
    {
        if(!$this->_file){
            $this->_file = new LECM_Connector_Action_File();
        }
        return $this->_file;
    }

    function resizeImage($src_path, $desc_path, $width = 0, $height = 0, $crop = false, $proportional = false, $quality = 100)
    {
        $result = false;
        if(($height <= 0 && $width <= 0) || !$this->exists($src_path)){
            return $result;
        }
        if($this->exists($desc_path)){
            $delete = $this->delete($desc_path);
            if(!$delete){
                return $result;
            }
        }
        $src_image = $this->getRealPath($src_path);
        $desc_image = $this->getRealPath($desc_path);
        $imageInfo = getimagesize($src_image);
        list($src_width, $src_height) = $imageInfo;
        $cropHeight = $cropWidth = 0;
        if ($proportional) {
            if($width  == 0){
                $factor = $height / $src_height;
            } elseif  ($height == 0){
                $factor = $width / $src_width;
            } else {
                $factor = min( $width / $src_width, $height / $src_height );
            }
            $final_width  = round( $src_width * $factor );
            $final_height = round( $src_height * $factor );
        } else {
            $final_width = ($width <= 0) ? $src_width : $width;
            $final_height = ($height <= 0) ? $src_height : $height;
            if($crop){
                $widthX = $src_width / $width;
                $heightX = $src_height / $height;
                $x = min($widthX, $heightX);
                $cropWidth = ($src_width - $width * $x) / 2;
                $cropHeight = ($src_height - $height * $x) / 2;
            }
        }
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($src_image);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($src_image);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($src_image);
                break;
            default:
                return false;
        }
        $image_resize = imagecreatetruecolor($final_width, $final_height);
        if (($imageInfo[2] == IMAGETYPE_GIF) || ($imageInfo[2] == IMAGETYPE_PNG)) {
            $transparency = imagecolortransparent($image);
            $pallet_size = imagecolorstotal($image);
            if ($transparency >= 0 && $transparency < $pallet_size) {
                $transparent_color  = imagecolorsforindex($image, $transparency);
                $transparency       = imagecolorallocate($image_resize, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($image_resize, 0, 0, $transparency);
                imagecolortransparent($image_resize, $transparency);
            } elseif ($imageInfo[2] == IMAGETYPE_PNG) {
                imagealphablending($image_resize, false);
                $color = imagecolorallocatealpha($image_resize, 0, 0, 0, 127);
                imagefill($image_resize, 0, 0, $color);
                imagesavealpha($image_resize, true);
            }
        }
        imagecopyresampled($image_resize, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $src_width - 2 * $cropWidth, $src_height - 2 * $cropHeight);
        switch ($imageInfo[2]) {
            case IMAGETYPE_GIF:
                $result = imagegif($image_resize, $desc_image);
                break;
            case IMAGETYPE_JPEG:
                $result = imagejpeg($image_resize, $desc_image, $quality);
                break;
            case IMAGETYPE_PNG:
                $quality = 9 - (int)((0.9 * $quality) /10.0);
                $result = imagepng($image_resize, $desc_image, $quality);
                break;
            default:
                return false;
        }
        return $result;
    }
}

class LECM_Connector_Action_Query extends LECM_Connector_Action
{
    function run()
    {
        $obj = array();
        $response = $this->getResponse();
        $cart = $this->getCart();
        if ($cart) {
            $dbConnect = LECM_Db::getInstance($cart);
            if (isset($_REQUEST['query']) && !$dbConnect->getError()) {
                $queries = @unserialize(base64_decode($_REQUEST['query']));
                if(isset($_REQUEST['serialize']) && $_REQUEST['serialize'] && $queries !== false){
                    foreach($queries as $key => $query){
                        if (is_array($query) && isset($query['type'])) {
                            $params = isset($query['params']) ? $query['params'] : null;
                            $obj[$key] = $dbConnect->processQuery($query['type'], $query['query'], $params);
                        } else {
                            $obj[$key] = $dbConnect->processQuery('select', $query);
                        }
                    }
                } elseif ($queries !== false) {
                    $query = $queries;
                    $params = isset($query['params']) ? $query['params'] : null;
                    $obj = $dbConnect->processQuery($query['type'], $query['query'], $params);
                } else {
                    $query = base64_decode($_REQUEST['query']);
                    $obj = $dbConnect->processQuery('select', $query);
                }
                $response->success(null, $obj);
                return;
            } else {
                $response->error('Can\'t connect to database or not run query! Error: ' . $dbConnect->getError(), null);
                return;
            }
        } else {
            $response->error('CMS Cart not found!', null);
            return;
        }
    }

}

abstract class LECM_Db
{
    static $instance = null;
    static $servers = array();
    var $server = 'localhost';
    var $user = 'root';
    var $password = '';
    var $database = '';
    var $link = null;
    var $response = null;
    var $error = null;

    abstract function connect();
    abstract function query($query);
    abstract function select($query);
    abstract function insert($query, $params);
    abstract function disconnect();

    function __construct($server, $user, $password, $database, $connect = true)
    {
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;

        $this->response = new LECM_Connector_Response();

        if ($connect) {
            $this->connect();
        }
    }

    function __destruct()
    {
        if ($this->link) {
            $this->disconnect();
        }
    }

    static function getInstance($cart) {
        if (!self::$instance) {
            $class = LECM_Db::getClass();
            self::$servers = array('server' => $cart->host, 'user' => $cart->username, 'password' => $cart->password, 'database' => $cart->database);
            self::$instance = new $class(
                self::$servers['server'],
                self::$servers['user'],
                self::$servers['password'],
                self::$servers['database']
            );
        }
        return self::$instance;
    }

    static function getClass()
    {
        $class = 'LECM_MySQL';
        if (extension_loaded('mysqli')) {
            $class = 'LECM_MySQLi';
        }

        return $class;
    }

    function getLink()
    {
        return $this->link;
    }

    function getError() {
        return $this->error;
    }

    function processQuery($type, $query, $params = null)
    {
        $result = null;
        switch($type){
            case 'select':
                $result = $this->select($query);
                break;
            case 'insert':
                $result = $this->insert($query, $params);
                break;
            case 'query':
                $result = $this->query($query);
                break;
            default:
                $result = $this->query($query);
                break;
        }
        return $result;
    }
}

class LECM_MySQL extends LECM_Db
{
    function connect() {
        if (!$this->link = @mysql_connect($this->server, $this->user, $this->password)) {
            $this->error = 'Link to database cannot be established.';
            return;
        }
        if (!mysql_select_db($this->database, $this->link)) {
            $this->error = 'The database selection cannot be made.';
            return;
        }
        if (!mysql_query('SET NAMES \'utf8\'', $this->link)) {
            $this->error = 'No utf-8 support. Please check your server configuration.';
            return;
        }
        return $this->link;
    }

    function disconnect() {
        mysql_close($this->link);
    }

    function query($sql) {
        return mysql_query($sql, $this->link);
    }

    function insert($sql, $params = null) {
        $result = $this->query($sql);
        if ($result && isset($params['insert_id'])) {
            $result = mysql_insert_id($this->link);
        }
        return $result;
    }

    function select($sql) {
        $data = array();
        $result = $this->query($sql);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }
}

class LECM_MySQLi extends LECM_Db
{
    function connect() {
        $socket = false;
        $port = false;
        if (strpos($this->server, ':') !== false) {
            list($server, $port) = explode(':', $this->server);
            if (is_numeric($port) === false) {
                $socket = $port;
                $port = false;
            }
        } elseif (strpos($this->server, '/') !== false) {
            $socket = $this->server;
        }
        if ($socket) {
            $this->link = @new mysqli(null, $this->user, $this->password, $this->database, null, $socket);
        } elseif ($port) {
            $this->link = @new mysqli($server, $this->user, $this->password, $this->database, $port);
        } else {
            $this->link = @new mysqli($this->server, $this->user, $this->password, $this->database);
        }
        if (mysqli_connect_error()) {
            $this->error = 'Link to database cannot be established: ' . mysqli_connect_error();
            return;
        }
        if (!$this->link->query('SET NAMES \'utf8\'')) {
            $this->error = 'No utf-8 support. Please check your server configuration.';
            return;
        }
        return $this->link;
    }

    function disconnect() {
        @$this->link->close();
    }

    function query($sql) {
        return $this->link->query($sql);
    }

    function insert($sql, $params = null) {
        $result = $this->query($sql);
        if ($result && isset($params['insert_id'])) {
            $result = $this->link->insert_id;
        }
        return $result;
    }

    function select($sql) {
        $data = array();
        $result = $this->query($sql);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }
}

class LECM_Connector_Adapter
{
    var $cart = null;
    var $host = 'localhost';
    var $username = 'root';
    var $password = '';
    var $database = '';
    var $tablePrefix = '';
    var $imageDir = '';
    var $imageDirCategory = '';
    var $imageDirProduct = '';
    var $imageDirManufacturer = '';
    var $version = '';
    var $charset = 'utf8';
    var $cookie_key = '';
    var $extend = '';
    var $check = false;

    function getCart($check = false) {
        $cart_type = $this->detectCartType();
        $this->cart = $this->getCartType($cart_type, $check);
        return $this->cart;
    }

    function getCartType($cart_type, $check) {
        $cart = null;
        $cart_type = strtolower($cart_type);
        $class_name = __CLASS__ . '_' . ucfirst($cart_type);
        if (class_exists($class_name)) {
            $cart = new $class_name();
            $cart->setCheck($check);
            $cart->setEnv();
        }
        return $cart;
    }

    function setCheck($check = false)
    {
        $this->check = $check;
    }

    function setEnv()
    {
        return $this;
    }

    function detectCartType() {
return "webshop";
        if (file_exists(LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php')) {

            // ZenCart
            if (file_exists(LECM_STORE_BASE_DIR . 'ipn_main_handler.php')) {
                return 'zencart';
            }

            // XtCommerce v3
            if (file_exists(LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.org.php')) {
                return 'xtcommerce';
            }

            // Loaded Commerce v6
            if (file_exists(LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure_dist.php')) {
                return 'loaded';
            }

            // TomatoCart
            if (file_exists(LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'toc_constants.php')) {
                return 'tomatocart';
            }

            // OsCommerce
            return 'oscommerce';
        }

        // VirtueMart
        if ((file_exists(LECM_STORE_BASE_DIR . 'configuration.php')) && (file_exists(LECM_STORE_BASE_DIR . '/components/com_virtuemart/virtuemart.php'))
        ) {
            return 'virtuemart';
        }

        //Mijoshop
        if ((file_exists(LECM_STORE_BASE_DIR . 'configuration.php')) && (file_exists(LECM_STORE_BASE_DIR . 'components/com_mijoshop/opencart/config.php'))
        ) {
            return 'opencart';
        }

        // WordPress
        if (file_exists(LECM_STORE_BASE_DIR . 'wp-config.php')) {
            // WooCommerce
            $wooCommerceDir = glob(LECM_STORE_BASE_DIR . 'wp-content/plugins/woocommerce*', GLOB_ONLYDIR);
            if (is_array($wooCommerceDir) && count($wooCommerceDir) > 0) {
                return 'woocommerce';
            }
            //Jigoshop
            $JigoshopDir = glob(LECM_STORE_BASE_DIR . 'wp-content/plugins/jigoshop*', GLOB_ONLYDIR);
            if (is_array($JigoshopDir) && count($JigoshopDir) > 0){
                return 'jigoshop';
            }

            // WP eCommerce
            return 'wpecommerce';
        }

        // XtCommerce v4
        if (file_exists(LECM_STORE_BASE_DIR . 'conf/config.php')) {
            return 'xtcommerce';
        }

        if (file_exists(LECM_STORE_BASE_DIR . 'config.php')) {

            // OpenCart
            if ((file_exists(LECM_STORE_BASE_DIR . 'system/startup.php') || (file_exists(LECM_STORE_BASE_DIR . 'common.php')) || (file_exists(LECM_STORE_BASE_DIR . 'library/locator.php')))
            ) {
                return 'opencart';
            }

            //Cs-Cart
            if (file_exists(LECM_STORE_BASE_DIR . 'config.local.php') || file_exists(LECM_STORE_BASE_DIR . 'partner.php')
            ) {
                return 'cscart';
            }

            // XCart
            return 'xcart';
        }

        //Prestashop
        if (file_exists(LECM_STORE_BASE_DIR . 'config/settings.inc.php')) {
            return 'prestashop';
        }

        // Loaded Commerce v7
        if (file_exists(LECM_STORE_BASE_DIR . 'includes/config.php')) {
            if (file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local.xml')) {
                return 'magento';
            }
            return 'loaded';
        }

        // Cube Cart
        if (file_exists(LECM_STORE_BASE_DIR . 'includes/global.inc.php')) {
            return 'cubecart';
        }

        if (file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local.xml')) {
            return 'magento';
        }


        //LDV94begin
        if (file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'config.php')
            &&file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'di.xml')
            &&file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'env.php')) {
            return 'magento';
        }
        //LDV94end


        // Interspire
        if (file_exists(LECM_STORE_BASE_DIR . 'config/config.php')) {
            return 'interspire';
        }

        // XCart 5
        if (file_exists(LECM_STORE_BASE_DIR . 'etc/config.php')) {
            return 'xcart';
        }

        //Oxid eShop
        if (file_exists(LECM_STORE_BASE_DIR . 'config.inc.php')){
            return 'oxideshop';
        }

        //Pinnacle
        if (@file_exists(LECM_STORE_BASE_DIR . 'content/engine/engine_config.php')) {
            return 'pinnaclecart';
        }
        //
        if ((file_exists(LECM_STORE_BASE_DIR . 'configuration.php')) && (file_exists(LECM_STORE_BASE_DIR . '/components/com_hikashop/hikashop.php'))
        ) {
            return 'hikashop';
        }

        return 'Not detect cart !';
    }

    function setHostPort($source) {
        $this->host = $source;
    }

    function getCartVersionFromDb($field, $tableName, $where)
    {
        $version = '';

        $sql = 'SELECT ' . $field . ' AS version FROM ' . $this->tablePrefix . $tableName . ' WHERE ' . $where;

        $dbConnect = LECM_Db::getInstance($this);
        if (!$dbConnect->getError()) {
            $result = $dbConnect->select($sql);
            if ($result) {
                $version = $result[0]['version'];
            }
        }

        return $version;
    }
}

class LECM_Connector_Adapter_Oscommerce extends LECM_Connector_Adapter
{
    function setEnv()
    {
        @require_once LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
        $this->setHostPort(DB_SERVER);
        $this->username = DB_SERVER_USERNAME;
        $this->password = DB_SERVER_PASSWORD;
        $this->database = DB_DATABASE;
        if ($this->check) {
            $this->imageDir = DIR_WS_IMAGES;
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            if (defined('DIR_WS_PRODUCT_IMAGES')) {
                $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
            }
            if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
            }
        }
    }
}

class LECM_Connector_Adapter_Virtuemart extends LECM_Connector_Adapter
{
    function setEnv()
    {
        @require_once LECM_STORE_BASE_DIR . '/configuration.php';
        $config = new JConfig();
        $this->setHostPort($config->host);
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        if ($this->check) {
            $this->tablePrefix = $config->dbprefix;

            $this->imageDir = 'components/com_virtuemart/shop_image/';
            $this->imageDirCategory    = $this->imageDir.'category/';
            $this->imageDirProduct      = $this->imageDir.'product/';
            $this->imageDirManufacturer = $this->imageDir.'manufacturer/';

            if (is_dir( LECM_STORE_BASE_DIR . 'images/stories/virtuemart/product')) {
                $this->imageDir = 'images/stories/virtuemart/';
                $this->imageDirCategory      = $this->imageDir . 'category/';
                $this->imageDirProduct    = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir.'manufacturer/';
            }
            if (file_exists(LECM_STORE_BASE_DIR . '/administrator/components/com_virtuemart/version.php')) {
                $ver = file_get_contents(LECM_STORE_BASE_DIR . '/administrator/components/com_virtuemart/version.php');
                if (preg_match('/\$RELEASE.+\'(.+)\'/', $ver, $match) != 0) {
                    $this->version = (string) $match[1];
                }
            }
        }
    }
}

class LECM_Connector_Adapter_Zencart extends LECM_Connector_Adapter
{
    function setEnv()
    {
        @require_once LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
        $this->username  = DB_SERVER_USERNAME;
        $this->password  = DB_SERVER_PASSWORD;
        $this->database    = DB_DATABASE;
        $this->setHostPort(DB_SERVER);
        if ($this->check) {
            $this->tablePrefix = DB_PREFIX;
            $this->imageDir = DIR_WS_IMAGES;
            $this->imageDirCategory    = $this->imageDir;
            $this->imageDirProduct      = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            if (defined('DIR_WS_PRODUCT_IMAGES')) {
                $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
            }
            if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
            }
            if (file_exists(LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'version.php')) {
                @require_once LECM_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'version.php';
                $major = PROJECT_VERSION_MAJOR;
                $minor = PROJECT_VERSION_MINOR;
                if (defined('EXPECTED_DATABASE_VERSION_MAJOR') && EXPECTED_DATABASE_VERSION_MAJOR != '') {
                    $major = EXPECTED_DATABASE_VERSION_MAJOR;
                }
                if (defined('EXPECTED_DATABASE_VERSION_MINOR') && EXPECTED_DATABASE_VERSION_MINOR != '') {
                    $minor = EXPECTED_DATABASE_VERSION_MINOR;
                }
                if ($major != '' && $minor != '') {
                    $this->version = $major . '.' . $minor;
                }
            }
            $this->charset = (defined('DB_CHARSET'))? DB_CHARSET : "";
        }
    }
}

class LECM_Connector_Adapter_Woocommerce extends LECM_Connector_Adapter
{
    function setEnv()
    {
        $config = file_get_contents(LECM_STORE_BASE_DIR . 'wp-config.php');

        preg_match('/define\s*\(\s*\'DB_NAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*\'DB_USER\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*\'DB_HOST\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->setHostPort($match[1]);
        if ($this->check) {
            preg_match('/define\s*\(\s*\'DB_CHARSET\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*\'(.*)\'\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = 'wp-content/uploads/';
            $this->imageDirCategory    = $this->imageDir;
            $this->imageDirProduct      = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'woocommerce_db_version'");
        }
    }
}

class LECM_Connector_Adapter_Jigoshop extends LECM_Connector_Adapter {

    function setEnv() {
        $config = file_get_contents(LECM_STORE_BASE_DIR . 'wp-config.php');

        preg_match('/define\s*\(\s*\'DB_NAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*\'DB_USER\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*\'DB_HOST\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->setHostPort($match[1]);
        if ($this->check) {
            preg_match('/define\s*\(\s*\'DB_CHARSET\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*\'(.*)\'\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = 'wp-content/uploads/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $jigoshop_cart_id = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'jigoshop_cart_id'");
            $this->version = $jigoshop_cart_id ? '2.0' : '1.0';
        }
    }

}

class LECM_Connector_Adapter_Prestashop extends LECM_Connector_Adapter
{
    function setEnv()
    {
        @require_once LECM_STORE_BASE_DIR . '/config/settings.inc.php';

        if (defined('_DB_SERVER_')) {
            $this->setHostPort(_DB_SERVER_);
        } else {
            $this->setHostPort(DB_HOSTNAME);
        }

        if (defined('_DB_USER_')) {
            $this->username = _DB_USER_;
        } else {
            $this->username = DB_USERNAME;
        }

        $this->password = _DB_PASSWD_;

        if (defined('_DB_NAME_')) {
            $this->database = _DB_NAME_;
        } else {
            $this->database = DB_DATABASE;
        }
        if ($this->check) {
            $this->tablePrefix = _DB_PREFIX_;
            $this->imageDir = '/img/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = _PS_VERSION_;
            $this->cookie_key = _COOKIE_KEY_;
        }
    }
}

class LECM_Connector_Adapter_Magento extends LECM_Connector_Adapter {
    function setEnv() {

        if(file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local.xml')){
            $config = file_get_contents(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local.xml');
            preg_match("/\<host\>\<\!\[CDATA\[(.+)\]\]>\<\/host\>/", $config, $match);
            $this->setHostPort($match[1]);
            preg_match("/\<username\>\<\!\[CDATA\[(.+)\]\]>\<\/username\>/", $config, $match);
            $this->username = $match[1];
            preg_match("/\<password\>\<\!\[CDATA\[(.*)\]\]>\<\/password\>/", $config, $match);
            $this->password = $match[1];
            preg_match("/\<dbname\>\<\!\[CDATA\[(.+)\]\]>\<\/dbname\>/", $config, $match);
            $this->database = $match[1];
            if ($this->check) {
                preg_match("/\<table_prefix\>\<\!\[CDATA\[(.*)\]\]>\<\/table_prefix\>/", $config, $match);
                $this->tablePrefix = $match[1];
                $this->imageDir = '/media/catalog/';
                $this->imageDirCategory = $this->imageDir . 'category/';
                $this->imageDirProduct = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
                if (file_exists(LECM_STORE_BASE_DIR . 'app/Mage.php')) {
                    $ver = file_get_contents(LECM_STORE_BASE_DIR . 'app/Mage.php');
                    if (preg_match("/getVersionInfo[^}]+\'major\' *=> *\'(\d+)\'[^}]+\'minor\' *=> *\'(\d+)\'[^}]+\'revision\' *=> *\'(\d+)\'[^}]+\'patch\' *=> *\'(\d+)\'[^}]+}/s", $ver, $match) == 1) {
                        $mageVersion = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];
                        $this->version = $mageVersion;
                        unset($match);
                    }
                }
            }
        }else{
            $config = file_get_contents(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'env.php');
            preg_match("/'host' => '(.+)',/", $config, $match);
            $this->setHostPort($match[1]);
            preg_match("/'username' => '(.+)',/", $config, $match);
            $this->username = $match[1];
            preg_match("/'password' => '(.*)',/", $config, $match);
            $this->password = $match[1];
            preg_match("/'dbname' => '(.+)',/", $config, $match);
            $this->database = $match[1];
            if ($this->check) {
                preg_match("/'table_prefix' => '(.*)',/", $config, $match);
                $this->tablePrefix = $match[1];
                $this->imageDir = '/pub/media/catalog/';
                $this->imageDirCategory = $this->imageDir . 'category/';
                $this->imageDirProduct = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
                if (file_exists(LECM_STORE_BASE_DIR . 'composer.json')) {
                    $ver = file_get_contents(LECM_STORE_BASE_DIR . 'composer.json');
                    if (preg_match("/\"version\": \"(.*)\",/", $ver, $match) == 1) {
                        // $mageVersion = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];
                        $this->version = $match[1];
                        unset($match);
                    }
                }
            }
        }
    }
}

class LECM_Connector_Adapter_Webshop extends LECM_Connector_Adapter {
    function setEnv() {

        if(file_exists(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc'.DIRECTORY_SEPARATOR.'config.json')){
            $config = file_get_contents(LECM_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'etc'.DIRECTORY_SEPARATOR.'config.json');
            $config = json_decode($config,true);
            $this->setHostPort($config['db_host']);
            $this->username = $config['db_username'];
            $this->password = $config['db_password'];
            $this->database = $config['db_name'];
            $this->tablePrefix = $config['db_prefix'];
            $this->imageDir = '/pub/images/';
            $this->imageDirCategory = $this->imageDir . 'category/';
            $this->imageDirProduct = $this->imageDir . 'product/';
        }
    }
}

class LECM_Connector_Adapter_Opencart extends LECM_Connector_Adapter{

    function setEnv() {

        if ((file_exists(LECM_STORE_BASE_DIR . 'configuration.php')) && (file_exists(LECM_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php'))) {
            @require_once LECM_STORE_BASE_DIR . 'configuration.php';
            $config = new JConfig();
            $this->setHostPort($config->host);
            $this->username = $config->user;
            $this->password = $config->password;
            $this->database = $config->db;
            if ($this->check) {
                //$this->tablePrefix = $config->dbprefix;
                $first_prefix = $config->dbprefix;

                $configFileContent = $baseFileContent = '';
                if (file_exists(LECM_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php')) {
                    $configFileContent = file_get_contents(LECM_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php');
                }
                if (file_exists(LECM_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php')) {
                    $baseFileContent = file_get_contents(LECM_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php');
                }

                preg_match("/define\(\"\DB_PREFIX\"\, \'(.+)\'\)/", $configFileContent, $match);
                $second_prefix = str_replace("#__", "", $match[1]);
                $this->tablePrefix = $first_prefix . $second_prefix;
                $this->imageDir = 'components/com_mijoshop/opencart/image/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                preg_match('/\$version.+\'(.+)\';/', $baseFileContent, $match);
                $this->version = $match[1];
            }
        } else {
            @require_once LECM_STORE_BASE_DIR . 'config.php';

            if (defined('DB_HOST')) {
                $this->setHostPort(DB_HOST);
            } else {
                $this->setHostPort(DB_HOSTNAME);
            }

            if (defined('DB_USER')) {
                $this->username = DB_USER;
            } else {
                $this->username = DB_USERNAME;
            }

            $this->password = DB_PASSWORD;

            if (defined('DB_NAME')) {
                $this->database = DB_NAME;
            } else {
                $this->database = DB_DATABASE;
            }
            if ($this->check) {
                $this->tablePrefix = DB_PREFIX;
                $this->imageDir = 'image/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;

                $indexFileContent = '';
                $startupFileContent = '';

                if (file_exists(LECM_STORE_BASE_DIR . '/index.php')) {
                    $indexFileContent = file_get_contents(LECM_STORE_BASE_DIR . '/index.php');
                }

                if (file_exists(LECM_STORE_BASE_DIR . '/system/startup.php')) {
                    $startupFileContent = file_get_contents(LECM_STORE_BASE_DIR . '/system/startup.php');
                }

                if (preg_match("/define\('\VERSION\'\, \'(.+)\'\)/", $indexFileContent, $match) == 0) {
                    preg_match("/define\('\VERSION\'\, \'(.+)\'\)/", $startupFileContent, $match);
                }

                if (count($match) > 0) {
                    $this->version = $match[1];
                }
            }
        }
    }
}

class LECM_Connector_Adapter_Interspire extends LECM_Connector_Adapter
{
    function setEnv()
    {
        @require_once LECM_STORE_BASE_DIR . 'config/config.php';
        $this->setHostPort($GLOBALS['ISC_CFG']['dbServer']);
        $this->username = $GLOBALS['ISC_CFG']['dbUser'];
        $this->password = $GLOBALS['ISC_CFG']['dbPass'];
        $this->database = $GLOBALS['ISC_CFG']['dbDatabase'];
        if ($this->check) {
            $this->tablePrefix = $GLOBALS['ISC_CFG']['tablePrefix'];
            $this->imageDir = $GLOBALS['ISC_CFG']['ImageDirectory'];
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;

            define('DEFAULT_LANGUAGE_ISO2',$GLOBALS['ISC_CFG']['Language']);

            $version = $this->getCartVersionFromDb('database_version', $GLOBALS['ISC_CFG']['tablePrefix'] . 'config', '1');
            if ($version != '') {
                $this->version = $version;
            }
        }
    }
}

class LECM_Connector_Adapter_Pinnaclecart extends LECM_Connector_Adapter {

    function setEnv() {

        @require_once LECM_STORE_BASE_DIR . 'content/engine/engine_config.php';

        //$this->Host = DB_HOST;
        $this->setHostPort(DB_HOST);
        $this->database = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;

        if ($this->check) {
            $this->imagesDir = 'images/';
            $this->imageDirCategory    = $this->imagesDir;
            $this->imageDirProduct      = $this->imagesDir;
            $this->imageDirManufacturer = $this->imagesDir;
            $version = $this->getCartVersionFromDb('value', (defined('DB_PREFIX') ? DB_PREFIX : '') . 'settings', "name = 'AppVer'");
            if ($version != '') {
                $this->version = $version;
            }
        }
    }
}

class LECM_Connector_Adapter_Wpecommerce extends LECM_Connector_Adapter
{
    function setEnv()
    {
        $config = file_get_contents(LECM_STORE_BASE_DIR . 'wp-config.php');
        preg_match("/define\(\'DB_NAME\', \'(.+)\'\);/", $config, $match);
        $this->database = $match[1];
        preg_match("/define\(\'DB_USER\', \'(.+)\'\);/", $config, $match);
        $this->username = $match[1];
        preg_match("/define\(\'DB_PASSWORD\', \'(.*)\'\);/", $config, $match);
        $this->password = $match[1];
        preg_match("/define\(\'DB_HOST\', \'(.+)\'\);/", $config, $match);
        $this->setHostPort($match[1]);
        if ($this->check) {
            preg_match("/(table_prefix)(.*)(')(.*)(')(.*)/", $config, $match);
            $this->tablePrefix = $match[4];

            $version = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'wpsc_version'");
            if ($version != '') {
                $this->version = $version;
            } else {
                if (file_exists(LECM_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-shopping-cart' . DIRECTORY_SEPARATOR . 'wp-shopping-cart.php')) {
                    $conf = file_get_contents(LECM_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-shopping-cart' . DIRECTORY_SEPARATOR . 'wp-shopping-cart.php');
                    preg_match("/define\('WPSC_VERSION.*/", $conf, $match);
                    if (isset($match[0]) && !empty($match[0])) {
                        preg_match("/\d.*/", $match[0], $project);
                        if (isset($project[0]) && !empty($project[0])) {
                            $version = $project[0];
                            $version = str_replace(array(' ', '-', '_', "'", ');', ')', ';'), '', $version);
                            if ($version != '') {
                                $this->version = strtolower($version);
                            }
                        }
                    }
                }
            }

            if (file_exists(LECM_STORE_BASE_DIR . 'wp-content/plugins/shopp/Shopp.php') || file_exists(LECM_STORE_BASE_DIR . 'wp-content/plugins/wp-e-commerce/editor.php')
            ) {
                $this->imageDir = 'wp-content/uploads/wpsc/';
                $this->imageDirCategory = $this->imageDir . 'category_images/';
                $this->imageDirProduct = $this->imageDir . 'product_images/';
                $this->imageDirManufacturer = $this->imageDir;
            } elseif (file_exists(LECM_STORE_BASE_DIR . 'wp-content/plugins/wp-e-commerce/wp-shopping-cart.php')) {
                $this->imageDir = 'wp-content/uploads/';
                $this->imageDirCategory = $this->imageDir . 'wpsc/category_images/';
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            } else {
                $this->imageDir = 'images/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }
}

class LECM_Connector_Adapter_Cubecart extends LECM_Connector_Adapter
{
    function setEnv() {
        $config = file_get_contents(LECM_STORE_BASE_DIR . '/includes/global.inc.php');
        preg_match("/glob\[\'dbhost\'\].+\'(.+)\';/", $config, $match);
        $this->setHostPort($match[1]);
        preg_match("/glob\[\'dbusername\'\].+\'(.+)\';/", $config, $match);
        $this->username = $match[1];
        preg_match("/glob\[\'dbpassword\'\].+\'(.*)\';/", $config, $match);
        $this->password = $match[1];
        preg_match("/glob\[\'dbdatabase\'\].+\'(.+)\';/", $config, $match);
        $this->database = $match[1];
        if ($this->check) {
            preg_match("/glob\[\'dbprefix\'\].+\'(.+)\';/", $config, $match);
            if ($match && $match[1]) {
                $this->tablePrefix = $match[1] . 'CubeCart_';
            } else {
                $this->tablePrefix = 'CubeCart_';
            }
            $this->imageDir = '/images/source/';
            if (file_exists(LECM_STORE_BASE_DIR . '/ini.inc.php')) {
                $config_local = file_get_contents(LECM_STORE_BASE_DIR . '/ini.inc.php');
            } else {
                $config_local = file_get_contents(LECM_STORE_BASE_DIR . '/includes/ini.inc.php');
            }
            preg_match("/define\(\'CC_VERSION\', \'(.+)\'\);/", $config_local, $match);
            if ($match) {
                $this->version = $match[1];
            } else {
                preg_match("/ini\[\'ver\'\].+\'(.*)\';/", $config_local, $match);
                $this->version = $match[1];
                $this->imageDir = '/images/uploads/';
            }
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
        }
    }
}

class LECM_Connector_Adapter_Oxideshop extends LECM_Connector_Adapter
{

    function setEnv(){

        $config = file_get_contents(LECM_STORE_BASE_DIR . 'config.inc.php');
        preg_match("/this->dbHost = '(.*)'/",$config,$match);
        $this->setHostPort($match[1]);
        preg_match("/this->dbName = '(.*)'/",$config,$match);
        $this->database = $match[1];
        preg_match("/this->dbUser = '(.*)'/",$config,$match);
        $this->username = $match[1];
        preg_match("/this->dbPwd = '(.*)'/",$config,$match);
        $this->password = $match[1];
        if ($this->check) {
            $this->version = $this->getCartVersionFromDb('OXVERSION', 'oxshops', "OXACTIVE = 1");
            if (file_exists(LECM_STORE_BASE_DIR . 'bootstrap.php')){
                @require_once LECM_STORE_BASE_DIR . 'bootstrap.php';
                $ox_lang = new oxLang();
                $languages = $ox_lang->getLanguageArray();
                $this->extend = $languages;

                $this->imageDir = 'out/pictures/master/';
                $this->imageDirCategory = $this->imageDir . 'category/thumb';
                $this->imageDirProduct = $this->imageDir . 'product';
                $this->imageDirManufacturer = $this->imageDir . 'manufacturer';
            }else{
                $this->imageDir = 'out/pictures';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }
}

class LECM_Connector_Adapter_Cscart extends LECM_Connector_Adapter
{
    function setEnv(){
        $config = file_get_contents(LECM_STORE_BASE_DIR . '/config.local.php');
        preg_match("/config\[\'db_host\'\].+\'(.+)\';/", $config, $match);
        $this->setHostPort($match[1]);
        preg_match("/config\[\'db_user\'\].+\'(.+)\';/", $config, $match);
        $this->username = $match[1];
        preg_match("/config\[\'db_password\'\].+\'(.*)\';/", $config, $match);
        $this->password = $match[1];
        preg_match("/config\[\'db_name\'\].+\'(.+)\';/", $config, $match);
        $this->database = $match[1];
        if ($this->check) {
            preg_match("/config\[\'table_prefix\'\].+\'(.+)\';/", $config, $match);
            if ($match) {
                $this->tablePrefix = $match[1];
            } else {
                $this->tablePrefix = 'cscart_';
            }
            $this->imageDir = '/images/detailed/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $config_local = file_get_contents(LECM_STORE_BASE_DIR . '/config.php');
            preg_match("/define\(\'PRODUCT_VERSION\', \'(.+)\'\);/", $config_local, $match);
            $this->version = $match[1];
        }
    }
}

class LECM_Connector_Adapter_Hikashop extends LECM_Connector_Adapter
{
    function setEnv(){

        @require_once LECM_STORE_BASE_DIR . '/configuration.php';
        $config = new JConfig();
        $this->setHostPort($config->host);
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        if ($this->check) {
            $this->tablePrefix = $config->dbprefix;

            $this->imageDir = 'images/com_hikashop/upload/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;

            $this->version = $this->getCartVersionFromDb('config_value', 'hikashop_config', "config_namekey = 'version'");
        }
    }
}

class LECM_Connector_Adapter_Xcart extends LECM_Connector_Adapter
{
    function setEnv(){

        if(file_exists(LECM_STORE_BASE_DIR . 'config.php')){
            $config = file_get_contents(LECM_STORE_BASE_DIR . 'config.php');

            preg_match('/\$sql_host.+\'(.+)\';/', $config, $match);
            $this->setHostPort($match[1]);
            preg_match('/\$sql_user.+\'(.+)\';/', $config, $match);
            $this->username = $match[1];
            preg_match('/\$sql_db.+\'(.+)\';/', $config, $match);
            $this->database = $match[1];
            preg_match('/\$sql_password.+\'(.*)\';/', $config, $match);
            $this->password = $match[1];
            if ($this->check) {
                $this->tablePrefix = 'xcart_';
                $this->imageDir = 'images/'; // xcart starting from 4.1.x hardcodes images location
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = $this->getCartVersionFromDb('value', 'config', "name = 'version'");
                preg_match('/\$blowfish_key.+\'(.*)\';/', $config, $match);
                $this->cookie_key = $match[1];
            }
        } else {
            $config = file_get_contents(LECM_STORE_BASE_DIR . 'top.inc.php');
            @require_once LECM_STORE_BASE_DIR . 'top.inc.php';
            $config = XLite::getInstance()->getOptions(array('database_details'));
            $this->setHostPort($config['hostspec']);
            $this->username = $config['username'];
            $this->database = $config['database'];
            $this->password = $config['password'];
            if ($this->check) {
                $this->tablePrefix = $config['table_prefix'];
                $this->imageDir = 'images/'; // xcart v5
                $this->imageDirCategory    = $this->imageDir . 'category/';
                $this->imageDirProduct      = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = $this->getCartVersionFromDb('value', 'config', "name = 'version'");
            }
        }
    }
}

error_reporting(1);

if (!isset($_SERVER)) {
    $_GET = &$HTTP_GET_VARS;
    $_POST = &$HTTP_POST_VARS;
    $_ENV = &$HTTP_ENV_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

define('LECM_ROOT_BASE_NAME', basename(getcwd()));
define('LECM_CONNECTOR_BASE_DIR', dirname(__FILE__));
define('LECM_STORE_BASE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

$connector = new LECM_Connector();
$connector->run();