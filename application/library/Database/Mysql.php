<?php

namespace Database;
use Exception;
use PDO;
use PDOException;

/**
 * mysql数据库ORM
 */
class Mysql{
    private static $_instance = [];
    private $config = null;
    private $pdo = null;
    private $persistent = false;
    private $statement = null;
    private $Transaction = false;
    private $table = null;
    private $where = null;
    private $group = null;
    private $order = null;
    private $limit = null;
    private $sql = null;

    private function __construct($config, $attr){
        $this->config = $config;
        $this->persistent = $attr;
    }

    /**
     * [getConnect 获取数据库连接]
     * @return [type] [description]
     */
    private function getConnect(){
        $this->pdo = self::db($this->config, $this->persistent);
    }

    /**
     * [db 创建数据库连接]
     * @param  [type]  $config     [数据库配置]
     * @param  boolean $persistent [是否长连接]
     */
    private static function db($config, $persistent = false){
        $options_arr = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$config->charset,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC);
        if($persistent === true){
            $options_arr[PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $dsn = $config->type.':dbname='.$config->database.';host=' .$config->hostname.';port='.$config->hostport;
            $pdo = new PDO($dsn,$config->username,$config->password,$options_arr);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
            return false;
        }

        if(!$pdo) {
            throw new Exception('PDO CONNECT ERROR');
            return false;
        }

        return $pdo;
    }

    /**
     * 得到操作数据库对象
     * @param string $dbname 对应的数据库是谁
     * @param bool $attr  是否长连接
     */
    public static function getInstance($config = null, $attr = false){
        $key = md5($config->database.$attr, true);
        if (!isset(self::$_instance[$key]) || !is_object(self::$_instance[$key])){
            self::$_instance[$key] = new self($config, $attr);
        }

        return self::$_instance[$key];
    }

    /**
     * [ 设置条件]
     * @param  string $table [表名]
     */
    public function table($table = ''){
        if (!empty($table) && is_string($table)) {
            $this->table = $this->config->prefix.$table;
        }

        return $this;
    }

    /**
     * [ 设置条件]
     * @param  string $limit [查询数量]
     */
    public function limit(int $limit = 1){
        if (is_numeric($limit)) {
            $this->limit = ' LIMIT '.$limit;
        }

        return $this;
    }

    /**
     * [ 设置条件]
     * @param  string $where [条件]
     */
    public function where($where = ''){
        if (!empty($where) && is_string($where)) {
            $this->where = ' WHERE '.$where;
        }

        return $this;
    }

    /**
     * [ 设置分组]
     * @param  string $group [分组]
     */
    public function group($group = ''){
        if (!empty($group) && is_string($group)) {
            $this->group = ' GROUP BY '.$group;
        }

        return $this;
    }

    /**
     * [ 设置排序]
     * @param  string $order [排序]
     */
    public function order($order = ''){
        if (!empty($order) && is_string($order)) {
            $this->order = ' ORDER BY '.$order;
        }

        return $this;
    }

    /**
     * [ 分页封装]
     * @param  integer $page     [表示从第几页开始取]
     * @param  integer $pageSize [表示每页多少条]
     */
    public function page($page = 0, $pageSize = 10){
        $page = intval($page);
        if ($page < 0) {
            return [];
        }

        $pageSize = intval($pageSize);
        $limit = '';
        if ($pageSize > 0) { // pageSize 为0时表示取所有数据
            $limit .= ' LIMIT ' . $pageSize;
            if ($page > 0) {
                $start_limit = ($page - 1) * $pageSize;
                $limit .= ' OFFSET ' . $start_limit;
            }
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * 查询多条记录操作
     * @param string $sql   执行查询的sql语句
     */
    public function select($field = '*'){
        $this->sql = 'SELECT '.$field.' FROM '.$this->table.$this->where.$this->group.$this->order.$this->limit;

        $this->free();
        $this->pdoExec($this->sql);
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询一条记录操作
     * @param string $sql   执行查询的sql语句
     */
    public function row($field = '*'){
        $this->sql = 'SELECT '.$field.' FROM '.$this->table.$this->where.$this->group.$this->order.' LIMIT 1';
        $this->free();
        $this->pdoExec($this->sql);
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 查询返回的条数   它只支持SELECT COUNT(*) FROM TABLE...或者SELECT COUNT(0) FROM TABLE...
     */
    public function count($field = '*'){
        $this->sql = 'SELECT COUNT('.$field.') FROM '.$this->table.$this->where;

        $this->free();
        if(!$this->pdoExec($this->sql)){
            return false;
        }

        return $this->statement->fetchColumn();
    }

    /**
     * [ 插入一条数据]
     * @param  [type] $data [数据]
     */
    public function insert($data = []){
        if (empty($data)){
            throw new \Exception('error: 缺少插入数据 data');
        }

        $insert_field = '';
        $insert_value = '';
        foreach ($data as $key => $value) {
            $insert_field .= '`'.$key.'`,';
            $insert_value .= '"'.addslashes($value).'",';
        }

        $insert_field = '('.substr($insert_field, 0, -1).')';
        $insert_value = '('.substr($insert_value, 0, -1).')';
        $this->sql = 'INSERT INTO '.$this->table.$insert_field.' VALUES '.$insert_value;
        return $this->execute($this->sql);
    }

    /**
     * [ 插入多条数据]
     * @param  [type] $data [数据]
     */
    public function insertAll($data = []){
        if (empty($data[0])){
            throw new \Exception('error: 缺少插入数据 $data');
        }

        $insert_field = '';
        $insert_value = '';
        foreach ($data as $k => $val) {
            if (is_array($val)) {
                $insert_field = '';
                $field_value = '';
                foreach ($val as $key => $value) {
                    $insert_field .= '`'.$key.'`,';
                    $field_value .= '"'.addslashes($value).'",';
                }

                $insert_field = '('.substr($insert_field, 0, -1).')';
                $insert_value .= '('.substr($field_value, 0, -1).'),';
            }
        }

        $insert_value = substr($insert_value, 0, -1);
        $this->sql = 'INSERT INTO '.$this->table.$insert_field.' VALUES '.$insert_value;
        return $this->execute($this->sql);
    }

    /**
     * [ 更新数据]
     * @param  [type] $data [数据]
     */
    public function update($data = []){
        if (empty($this->where)){
            throw new \Exception('error: 缺少更新条件 where');
        }

        if (empty($this->where) || empty($data)){
            throw new \Exception('error: 缺少更新数据 data');
        }

        $update_value = '';
        foreach ($data as $key => $value) {
            $update_value .= $key.' = "'.addslashes($value).'",';
        }

        $update_value = substr($update_value, 0, -1);
        $this->sql = 'UPDATE '.$this->table.' SET '.$update_value.$this->where;
        return $this->execute($this->sql);
    }

    /**
     * [ 字段自增]
     */
    public function setInc($field = '', $step = 1){
        if (empty($this->where)){
            throw new \Exception('error: 缺少删除条件 where');
        }

        if (empty($field)){
            throw new \Exception('error: 缺少更新字段');
        }

        $this->sql = 'UPDATE '.$this->table. ' SET '. $field .'='. $field .'+'. $step .$this->where;
        return $this->execute($this->sql);
    }

    /**
     * [ 字段自减]
     */
    public function setDec($field = '', $step = 1){
        if (empty($this->where)){
            throw new \Exception('error: 缺少删除条件 where');
        }

        if (empty($field)){
            throw new \Exception('error: 缺少更新字段');
        }

        $this->sql = 'UPDATE '.$this->table. ' SET '. $field .'='. $field .'-'. $step .$this->where;
        return $this->execute($this->sql);
    }

    /**
     * [ 返回条数 只支持SELECT COUNT(*) FROM 或者SELECT COUNT(0) FROM ]
     */
    public function delete(){
        if (empty($this->where)){
            throw new \Exception('error: 缺少删除条件 where');
        }

        $this->sql = 'DELETE FROM '.$this->table.$this->where;
        return $this->execute($this->sql);
    }

    /**
     * 这个是用来进行添加 删除  修改操作  使用事务操作
     * @param string $sql   执行查询的sql语句
     */
    public function execute($sql){
        if (!is_string($sql)){
            return false;
        }

        $this->free();

        try{
            return $this->pdoExec($sql);
        } catch (Exception $e) {
            throw new Exception('Error Execute:'.$e->getMessage());
            return false;
        }
    }

    /**
     * 开启事务
     */
    public function startTrans(){
        $this->Transaction = true;
        //$this->getConnect();
        $this->free();
        $this->pdo->beginTransaction();//开启事务
    }

    /**
     * 提交事务
     */
    public function commit(){
        if($this->Transaction === true && is_object($this->pdo)){
            $this->pdo->commit();
        }

        $this->Transaction = false;
        unset($this->pdo);
    }

    /**
     * 回滚事务
     */
    public function rollback(){
        if($this->Transaction === true && is_object($this->pdo)){
            $this->pdo->rollback();
        }

        $this->Transaction = false;
        unset($this->pdo);
    }

    /**
     * 返回 lastInsertId
     */
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }

    /**
     * [lastSql 最后一条执行sql]
     */
    public function lastSql(){
        return $this->sql;
    }

    /**
     * 内部调用方法  用来直接执行SQL语句的方法
     */
    private function pdoExec($sql){
        $this->statement = $this->pdo->prepare($sql);
        if (false === $this->statement){
            return false;
        }

        $res = $this->statement->execute();
        if (!$res){
            throw new Exception('sql:'.$sql.'<====>error:'.json_encode($this->statement->errorInfo()));
        }else{
            return $res;
        }
    }

    /**
     * 内部调用方法  用来释放的
     */
    private function free(){
        if (is_null($this->pdo)){
            $this->getConnect();
        }

        if (!empty($this->statement)){
            $this->statement->closeCursor();
            $this->statement = null;
        }

        $this->table = null;
        $this->where = null;
        $this->group = null;
        $this->order = null;
        $this->limit = null;
    }
}