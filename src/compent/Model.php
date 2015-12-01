<?php
namespace Wx\Wxserver\Compent;
/**
 * 数据库操作层
 * @author xiawei
 */
abstract class Model {
	/**
	 * model适配器
	 * @var ModelDriver
	 */
	private $modelDriver;
	/**
	 * 构造方法
	 * @param ModelDriver $modelDriver
	 */
	public function __construct(ModelDriver $modelDriver) {
		$this->modelDriver = $modelDriver;
	}
	
	/**
	 * 获取一个Model
	 * @param ModelDriver $modelDriver
	 * @param string $className
	 */
	public static function model(ModelDriver $modelDriver, $className) {
		return $modelDriver->getModel($className);
	}
	
	
	/**
	 * 获取数据库连接
	 * @return Ambigous <\Wx\Wxdb\DBPoolConnection, \Wx\Wxdb\DBConnection>
	 */
	public function db() {
		return $this->modelDriver->db();
	}
	
	
	/**
	 * 获取读数据库连接
	 * @return \Wx\Wxdb\DBConnection
	 */
	public function dbRead() {
		return $this->modelDriver->dbRead();
	}
	
	/**
	 * 获取写数据库连接
	 * @return \Wx\Wxdb\DBConnection
	 */
	public function dbWrite() {
		return $this->modelDriver->dbWrite();
	}
	
	/**
	 * Model对应的数据库表明
	 * @return string
	 */
	abstract public function tableName();
	
	/**
	 * 通过Id来获取数据
	 * @param integer $id 对应的Id
	 * @param string $fields 要查询的字段
	 * @return array
	 */
	public function getById($id, $fields = '*') {
		return $this->dbRead()->createCommand()->select($fields)->from($this->tableName())->where(array('id' => $id))->queryRow();
	}
	
	/**
	 * 获取所有的数据
	 * @param string $fields 要查询的字段
	 */
	public function getAll($fields = '*') {
		return $this->dbRead()->createCommand()->select($fields)->from($this->tableName())->queryAll();
	}
}