<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Wx\Wxdb\DBPoolConnection;
use Wx\Wxdb\DBConnection;
/**
 * Model层的适配器
 * @author xiawei
 */
class ModelDriver {
	private $db;
	private $dbWrite;
	private $dbRead;
	private $models = array();
	/**
	 * 获取到数据库连接
	 * @return Ambigous <\Wx\Wxdb\DBPoolConnection, \Wx\Wxdb\DBConnection>
	 */
	private function db() {
		if (empty($this->db)) {
			$dbConfig = ApiConfig::getDBConfig();
			if (!isset($dbConfig['db'])) {
				ApiServerException::throwException('配置错误', ApiServerException::ERROR_CODE_DB_ERROR);
			}
			$config = $dbConfig['db'];
			if (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$this->db = new DBPoolConnection(
					$config['dns'],
					$config['username'],
					$config['password'],
					isset($config['charset']) ? $config['charset'] : 'utf8',
					isset($config['options']) ? $config['options'] : array()
				);
			} else {
				$this->db = new DBConnection(
					$config['dns'],
					$config['username'],
					$config['password'],
					isset($config['charset']) ? $config['charset'] : 'utf8',
					isset($config['options']) ? $config['options'] : array()
				);
			}
		}
		return $this->db;
	
	}
	
	/**
	 * 获取写数据库
	 * @return Ambigous <\Wx\Wxdb\DBPoolConnection, \Wx\Wxdb\DBConnection>
	 */
	public function dbWrite() {
		if (empty($this->dbWrite)) {
			$dbConfig = ApiConfig::getDBConfig();
			if (!isset($dbConfig['write'])) {
				$this->dbWrite = $this->db();
				return $this->dbWrite;
			}
			$dbConfigWrite = $dbConfig['write'];
			if (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$this->dbWrite = new DBPoolConnection(
					$dbConfigWrite['dns'],
					$dbConfigWrite['username'],
					$dbConfigWrite['password'],
					isset($dbConfigWrite['charset']) ? $dbConfigWrite['charset'] : 'utf8',
					isset($dbConfigWrite['options']) ? $dbConfigWrite['options'] : array()
				);
			} else {
				$this->dbWrite = new DBConnection(
					$dbConfigWrite['dns'],
					$dbConfigWrite['username'],
					$dbConfigWrite['password'],
					isset($dbConfigWrite['charset']) ? $dbConfigWrite['charset'] : 'utf8',
					isset($dbConfigWrite['options']) ? $dbConfigWrite['options'] : array()
				);
			}
		}
		return $this->dbWrite;
	}
	
	
	/**
	 * 获取读数据库
	 * @return Ambigous <\Wx\Wxdb\DBPoolConnection, \Wx\Wxdb\DBConnection>
	 */
	public function dbRead() {
		if (empty($this->dbRead)) {
			$dbConfig = ApiConfig::getDBConfig();
			if (!isset($dbConfig['read'])) {
				$this->dbRead = $this->db();
				return $this->dbRead;
			}
			$dbConfigRead = $dbConfig['read'];
			if (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$this->dbRead = new DBPoolConnection(
					$dbConfigRead['dns'],
					$dbConfigRead['username'],
					$dbConfigRead['password'],
					isset($dbConfigRead['charset']) ? $dbConfigRead['charset'] : 'utf8',
					isset($dbConfigRead['options']) ? $dbConfigRead['options'] : array()
				);
			} else {
				$this->dbRead = new DBConnection(
					$dbConfigRead['dns'],
					$dbConfigRead['username'],
					$dbConfigRead['password'],
					isset($dbConfigRead['charset']) ? $dbConfigRead['charset'] : 'utf8',
					isset($dbConfigRead['options']) ? $dbConfigRead['options'] : array()
				);
			}
		}
		return $this->dbRead;
	}
	
	/**
	 * 获取一个Model
	 * @param string $className
	 * @return multitype:
	 */
	public function getModel($className) {
		if (!isset($this->models[$className])) {
			$this->models[$className] = new $className($this);
		}
		return $this->models[$className];
	}
	
	
	public function __destruct() {
		unset($this->db);
		unset($this->dbRead);
		unset($this->dbWrite);
	}
}