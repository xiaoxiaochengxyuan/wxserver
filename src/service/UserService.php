<?php
namespace Wx\Wxserver\Service;
use Wx\Wxserver\Compent\Service;
use Wx\Wxserver\Model\UserModel;
use Wx\Wxserver\Compent\ModelDriver;
class UserService extends Service {
	public function getAll() {
		$modelDriver = $this->modelDriver();
		$userModel = UserModel::model($modelDriver);
		return $userModel->getAll();
	}
}