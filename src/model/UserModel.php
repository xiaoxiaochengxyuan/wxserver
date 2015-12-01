<?php
namespace Wx\Wxserver\Model;
use Wx\Wxserver\Compent\Model;
use Wx\Wxserver\Compent\ModelDriver;
/**
 * 用户表对应的Model
 * @author xiawei
 */
class UserModel extends Model {
	/**
	 * 单例
	 * @param ModelDriver $modelDriver
	 * @param system $className
	 * @return UserModel
	 */
	public static function model(ModelDriver $modelDriver, $className = __CLASS__) {
		return $modelDriver->getModel($className);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Wx\Wxserver\Compent\Model::tableName()
	 */
	public function tableName() {
		return 'user';
	}
}