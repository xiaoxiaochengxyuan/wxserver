<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Wx\Wxdb\DBConnection;
use Wx\Wxdb\DBPoolConnection;
/**
 * 所有服务类的基类
 * @author xiawei
 */
abstract class Service {
	private $db = null;
	/**
	 * 数据库读连接
	 * @var DBConnection
	 */
	private $dbReader = null;
	/**
	 * 数据库写连接
	 * @var DBConnection
	 */
	private $dbWriter = null;
	
	/**
	 * 获取读数据库连接
	 * @return \Wx\Wxdb\DBConnection
	 */
	protected function dbReader() {
		if (empty($this->dbReader)) {
			$this->dbReader = $this->db('reader');
		}
		return $this->dbReader;
	}
	
	/**
	 * 获取数据库写连接
	 * @return \Wx\Wxdb\DBConnection
	 */
	protected function dbWriter() {
		if (empty($this->dbWriter)) {
			$this->dbWriter = new $this->db('writer');
		}
		return $this->dbWriter;
	}
	
	private function db($type) {
		if (!\in_array($type, array('reader', 'writer'))) {
			ApiServerException::throwException('获取DB的参数只能是reader和writer', ApiServerException::ERROR_CODE_RUNTIME_EXCEPTION);
		}
		
		/*
		 * 首先检查对应的连接是否已经被初始化,如果被初始化了,直接返回
		 */
		if ($type == 'reader' && !empty($this->dbReader)) {
			return $this->dbReader;
		}
		if ($type == 'writer' && !empty($this->dbWriter)) {
			return $this->dbWriter;
		}
		$dbConfig = ApiConfig::getDBConfig();
		if ($type == 'reader') {
			if (!isset($dbConfig['read']) || !isset($dbConfig['write'])) {
				$this->dbReader = $this->getDb($dbConfig);
				return $this->dbReader;
			} elseif (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$dbReaderConfig = $dbConfig['read'][\rand(0, \count($dbConfig['read'] - 1))];
				$this->dbReader = new DBPoolConnection(
					$dbReaderConfig['dns'],
					$dbReaderConfig['username'],
					$dbReaderConfig['password'],
					$dbReaderConfig['charset'],
					isset($dbReaderConfig['options']) ? $dbReaderConfig['options'] : array()
				);
				return $this->dbReader;
			} else {
				$dbReaderConfig = $dbConfig['read'][\rand(0, \count($dbConfig['read'] - 1))];
				$this->dbReader = new DBConnection(
					$dbReaderConfig['dns'],
					$dbReaderConfig['username'],
					$dbReaderConfig['password'],
					$dbReaderConfig['charset'],
					isset($dbReaderConfig['options']) ? $dbReaderConfig['options'] : array()
				);
				return $this->dbReader;
			}
		}
		
		
		if ($type == 'writer') {
			if (!isset($dbConfig['write']) || !isset($dbConfig['read'])) {
				$this->dbWriter = $this->getDb($dbConfig);
				return $this->dbWriter;
			} elseif (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$this->dbWriter = new DBPoolConnection(
					$dbConfig['write']['dns'],
					$dbConfig['write']['username'],
					$dbConfig['write']['password'],
					$dbConfig['write']['charset'],
					isset($dbConfig['write']['options']) ? $dbConfig['write']['options'] : array()
				);
				return $this->dbWriter;
			} else {
				$this->dbWriter = new DBConnection(
					$dbConfig['write']['dns'],
					$dbConfig['write']['username'],
					$dbConfig['write']['password'],
					$dbConfig['write']['charset'],
					isset($dbConfig['write']['options']) ? $dbConfig['write']['options'] : array()
				);
				return $this->dbWriter;
			}
		}
	}
	
	/**
	 * 获取一个数据库连接
	 * @param array $dbConfig
	 * @return Ambigous <\Wx\Wxdb\DBPoolConnection, \Wx\Wxdb\DBConnection>
	 */
	private function getDb(array $dbConfig) {
		if (empty($this->db)) {
			if (empty($dbConfig['db'])) {
				ApiServerException::throwException('数据库配置错误', ApiServerException::ERROR_CODE_DB_ERROR);
			}
			if (isset($dbConfig['pool']) && $dbConfig['pool']) {
				$this->db = new DBPoolConnection(
					$dbConfig['db']['dns'],
					$dbConfig['db']['username'],
					$dbConfig['db']['password'],
					$dbConfig['db']['charset'],
					isset($dbConfig['db']['options']) ? $dbConfig['db']['options'] : array()
				);
			} else {
				$this->db = new DBConnection(
					$dbConfig['db']['dns'],
					$dbConfig['db']['username'],
					$dbConfig['db']['password'],
					$dbConfig['db']['charset'],
					isset($dbConfig['db']['options']) ? $dbConfig['db']['options'] : array()
				);
			}
		}
		return $this->db;
	}
	
	/**
	 * 获取表明
	 * @return string
	 */
	abstract public function tableName();
	
	/**
	 * 通过Id获取一条数据
	 * @param integer $id 要获取的数据的Id
	 * @param string $fields 要获取的字段
	 * @return array
	 */
	public function getById($id, $fields = '*') {
		return $this->dbReader()->createCommand()
			->select($fields)
			->from($this->tableName())
			->where(array('id' => $id))
			->queryRow();
	}
	
	/**
	 * 通过某个字段来获取一条数据
	 * @param string $fieldName  条件字段名
	 * @param string $fieldValue 条件字段值
	 * @param string $fields     要获取那些字段
	 * @return array
	 */
	public function getByField($fieldName, $fieldValue, $fields = '*') {
		return $this->dbReader()->createCommand()
			->select($fields)
			->from($this->tableName())
			->where(array($fieldName => $fieldValue))
			->queryRow();
	}
	
	
	/**
	 * 通过某个字段来获取所有的数据
	 * @param string $fieldName  条件字段名
	 * @param string $fieldValue 条件字段值
	 * @param string $fields     要获取那些字段
	 * @return array
	 */
	public function getAllByField($fieldName, $fieldValue, $fields = '*') {
		return $this->dbReader()->createCommand()
			->select($fields)
			->from($this->tableName())
			->where(array($fieldName => $fieldValue))
			->queryAll();
	}
	
	/**
	 * 通过Id来获取某个字段
	 * @param string $fieldName
	 * @param integer $id
	 * @return string
	 */
	public function getFieldById($field, $id) {
		return $this->dbReader()->createCommand()
			->select($field)
			->from($this->tableName())
			->where(array('id' => $id))
			->queryScalar();
	}
	
	/**
	 * 通过某个字段来获取某个字段
	 * @param string $field
	 * @param string $fieldName
	 * @param string $fieldValue
	 * @return string
	 */
	public function getFieldByField($field, $fieldName, $fieldValue) {
		return $this->dbReader()->createCommand()
			->select($field)
			->from($this->tableName())
			->where(array($fieldName => $fieldValue))
			->queryScalar();
	}
	
	
	/**
	 * 加载整张表
	 * @return array
	 */
	public function getAll() {
		return $this->dbReader()->createCommand()->select()->from($this->tableName())->queryAll();
	}
	
	/**
	 * 通过Id删除数据
	 * @param integer $id
	 * @return integer 影响行数
	 */
	public function deleteById($id) {
		return $this->dbWriter()->createCommand()->delete($this->tableName(), array('id' => $id));
	}
	
	/**
	 * 通过某个字段删除数据
	 * @param string $fieldName  条件字段的名称
	 * @param string $fieldValue 条件字段的值
	 * @return integer 影响行数
	 */
	public function deleteByField($fieldName, $fieldValue) {
		return $this->dbWriter()->createCommand()->delete($this->tableName(), array($fieldName => $fieldValue));
	}
	
	/**
	 * 添加一条数据
	 * @param array $data
	 * @return boolean 成功还是失败
	 */
	public function insert(array $data) {
		return $this->dbWriter()->createCommand()->insert($this->tableName(), $data);
	}
	
	/**
	 * 根据Id修改一条数据
	 * @param integer $id 要修改的数据的Id
	 * @param array $data 要修改的数据
	 * @return boolean 是否修改成功
	 */
	public function update($id, array $data) {
		return $this->dbWriter()->createCommand()->update($this->tableName(), $data, array('id' => $id));
	}
}