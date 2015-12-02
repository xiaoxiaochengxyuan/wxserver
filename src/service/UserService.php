<?php
namespace Wx\Wxserver\Service;
use Wx\Wxserver\Compent\Service;
class UserService extends Service {
	const TABLE_NAME = 'user';
	/**
	 * (non-PHPdoc)
	 * @see \Wx\Wxserver\Compent\Service::tableName()
	 */
	public function tableName() {
		return self::TABLE_NAME;
	}
}