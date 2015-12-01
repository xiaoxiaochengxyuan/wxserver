<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Wx\Wxutils\NetUtil;
use Wx\Wxutils\StringUtil;
/**
 * Api服务器
 * @author xiawei
 */
class ApiServer {
	private static $APISERVER = null;
	private $serv = null;
	/**
	 * 构造方法
	 */
	private function __construct() {
	}
	/**
	 * 获取Api服务器单例
	 * @return \Wx\Wxserver\Compent\ApiServer
	 */
	public static function app() {
		if (self::$APISERVER == null) {
			self::$APISERVER = new self();
		}
		return self::$APISERVER;
	}
	
	/**
	 * 启动服务器
	 */
	public function run() {
		$swooleServerConfig = ApiConfig::swServerConfig();
		if (self::isRun()) {
			ApiServerException::throwException('api server has running,can not run again', ApiServerException::ERROR_CODE_CAN_NOT_RUN_API_SERVER);
		} else {
			$this->serv = new \swoole_server($swooleServerConfig['ip'], $swooleServerConfig['port']);
			$this->initConfig($swooleServerConfig);
			//这里是注册receive方法
			$this->registerCallBack();
			$this->serv->start();
		}
		return true;
	}
	
	/**
	 * 初始化Swoole服务器的配置
	 */
	private function initConfig($swooleServerConfig) {
		unset($swooleServerConfig['ip']);
		unset($swooleServerConfig['port']);
		$this->serv->set($swooleServerConfig);
	}
	
	/**
	 * 注册回调方法
	 */
	private function registerCallBack() {
		$this->serv->on('connect', array($this, 'onConnect'));
		$this->serv->on('receive', array($this, 'onReceive'));
		$this->serv->on('close', array($this, 'onClose'));
	}
	
	/**
	 * 客户端连接到服务器时候的回调方法
	 * @param unknown $serv
	 * @param unknown $fd
	 */
	public function onConnect($serv, $fd) {
	}
	
	/**
	 * 接受数据之后的回调方法
	 * @param swoole_ $serv
	 * @param unknown $fd
	 * @param unknown $from_id
	 * @param unknown $data
	 */
	public function onReceive($serv, $fd, $from_id, $data) {
		$data = StringUtil::decryStr($data, ApiConfig::ENCRYTP_DECRYPT_SALT);
		$receiveData = \json_decode($data, true);
		if (empty($receiveData)) {
			$this->clientResult($serv, $fd, false, '对不起,您上传的数据有误!');
		} else {
			if (isset($receiveData['type']) && $receiveData['type'] == 'server') {
				switch ($receiveData['action']) {
					case 'stop' :
						$serv->send($fd, '1');
						$serv->close($fd);
						$serv->shutdown();
						break;
					case 'reload' :
						$serv->send($fd, '1');
						$serv->close($fd);
						$serv->reload();
						break;
				}
			} elseif (isset($receiveData['type']) && $receiveData['type'] == 'api-call' && isset($receiveData['app']) && isset($receiveData['username']) && isset($receiveData['password']) && isset($receiveData['params'])) {
				$app = $receiveData['app'];
				$appConfig = ApiConfig::appsConfig();
				if (!isset($appConfig[$app])) {
					$this->clientResult($serv, $fd, false, '您的应用没有权限访问Api服务器,请联系Api管理员添加您的应用!');
				} elseif ($receiveData['username'] != $appConfig[$app]['username'] || $receiveData['password'] != $appConfig[$app]['password']) {
					$this->clientResult($serv, $fd, false, '您的应用的username或者password错误,请检查!');
				} elseif (!isset($receiveData['service']) || !isset($receiveData['method'])) {
					$this->clientResult($serv, $fd, false, '您没有提交您要请求的服务或者方法,请检查!');
				} else {
					$serviceName = $receiveData['service'];
					$serviceName = \ucfirst($serviceName).'Service';
					$serviceName = 'Wx\\Wxserver\\Service\\'.$serviceName;
					if (!\class_exists($serviceName)) {
						$this->clientResult($serv, $fd, false, '您要请求的服务不存在,请检查!');
					} else {
						$reflectionClass = Service::refInstance($serviceName);
						$service = Service::getService($serviceName);
						$methodName = $receiveData['method'];
						if (!$reflectionClass->hasMethod($methodName)) {
							$this->clientResult($serv, $fd, false, '您请求的服务不存在,请检查');
						} else {
							$reflectionMethod = $reflectionClass->getMethod($methodName);
							if (!$reflectionMethod->isPublic()) {
								$this->clientResult($serv, $fd, false, '您请求的服务尚未开放,请检查');
							} else {
								try {
									$data = $reflectionMethod->invokeArgs($service, $receiveData['params']);
									$this->clientResult($serv, $fd, true, null, $data);
								} catch (\Exception $ex) {
									$this->clientResult($serv, $fd, false, $ex->getMessage());
								}
							}
						}
					}
				}
			} else {
				$this->clientResult($serv, $fd, false, '对不起,您无权访问服务器,提交参数不完整,请检查!');
			}
		}
	}
	
	/**
	 * 返回数据到对应的客户端
	 * @param unknown $sev
	 * @param unknown $fd
	 * @param unknown $success
	 * @param string $message
	 * @param string $data
	 */
	private function clientResult($serv, $fd, $success, $message = null, $data = null) {
		$result = array('succ' => $success, 'msg' => $message, 'data' => $data);
		$jsonResult = \json_encode($result);
		$strResult = StringUtil::encryStr($jsonResult, ApiConfig::ENCRYTP_DECRYPT_SALT);
		$serv->send($fd, $strResult);
		$serv->close($fd);
	}
	
	/**
	 * 客户端关闭时候的回调方法
	 * @param unknown $serv
	 * @param unknown $fd
	 */
	public function onClose($serv, $fd) {
	}
	
	/**
	 * 判断Api服务器是不是已经启动
	 */
	public static function isRun() {
		$swooleServerConfig = ApiConfig::swServerConfig();
		return NetUtil::checkPortUsed($swooleServerConfig['ip'], $swooleServerConfig['port']);
	}
	
	/**
	 * 停止Api服务器
	 * @return boolean 停止成功返回true,否则返回false
	 */
	public function stop() {
		if (!ApiServer::isRun()) {
			ApiServerException::throwException('The Api Server is not running,Can not stop!', ApiServerException::ERROR_CODE_API_SERVER_IS_NOT_RUNNING);
		}
		$result = ApiClient::instance()->sendData(array('type' => 'server', 'action' => 'stop'));
		return $result == 1;
	}
	
	/**
	 * 柔性重启服务器
	 * @return boolean 重启成功返回true,否则返回false
	 */
	public function reload() {
		if (!ApiServer::isRun()) {
			ApiServerException::throwException('The Api Server is not running,Can not reload!', ApiServerException::ERROR_CODE_API_SERVER_IS_NOT_RUNNING);
		}
		$result = ApiClient::instance()->sendData(array('type' => 'server', 'action' => 'reload'));
		return $result == 1;
	}
}