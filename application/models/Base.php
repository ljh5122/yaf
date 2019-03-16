<?php

/**
* 模型基础类
*/
class BaseModel {
	protected static $_instance 	= null;	//模型对象
	protected $table 				= null;	//模型对应表
	protected $db 					= null; //数据库对象
	protected $db_config 			= null; //数据库配置
	protected $cache 				= null; //缓存对象
	protected $cache_config 		= null;	//缓存配置
	

	private final function __construct(){}
	private final function __clone(){}
	
	// 获取实例对象
	public static function getInstance(){
		$class =  get_called_class();
		if (!isset(static::$_instance[$class])) {
			static::$_instance[$class] = new static();
		}

		return static::$_instance[$class];
	}

	/**
	 * [ 获取mysql实例对象]
	 */
	public function db(string $table = ''){
		if ($this->db_config == null) {
            $this->db_config = \Yaf\Registry::get('config')->database->master;
		}

		$this->db = \Database\Mysql::getInstance($this->db_config, false);
		if(!empty($table)){
			$this->table = $table;
		}

		$this->db->table($this->table);
		return $this->db;
	}

	/**
	 * [ 获取cache实例对象]
	 */
	public function cache(){
		if ($this->cache_config == null) {
			$config = \Yaf\Registry::get('config')->cache;
		}

		$class = 'Cache\\driver\\' . ucwords($config->type);
		if ($this->cache == null) {
			//文件缓存(默认)
			$this->cache_config = [
				'expire'        => $config->file->expire,
				'prefix'        => $config->file->prefix,
				'path'          => $config->file->path,
			];

			//redis缓存
			if($config->type == 'redis'){
				$this->cache_config = [
					'host'       => $config->redis->host,
					'port'       => $config->redis->port,
					'password'   => $config->redis->password,
					'select'     => $config->redis->select,
					'timeout'    => $config->redis->timeout,
					'expire'     => $config->redis->expire,
					'prefix'     => $config->redis->prefix,
				];
			}

			//memcached缓存
			if($config->type == 'memcached'){
				$this->cache_config = [
					'host'     => $config->memcached->host,
					'port'     => $config->memcached->port,
					'expire'   => $config->memcached->expire,
					'timeout'  => $config->memcached->timeout,
					'prefix'   => $config->memcached->prefix,
					'username' => $config->memcached->username,
					'password' => $config->memcached->password,
				];
			}

			$this->cache = new $class($this->cache_config);
		}

		return $this->cache;
	}
}