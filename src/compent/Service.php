<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Wx\Wxdb\DBConnection;
use Wx\Wxdb\DBPoolConnection;
/**
 * 所有服务类的基类
 * @author xiawei
 */
class Service {
	/**
	 * 存放所有反射类的容器
	 * @var array
	 */
	private static $reflectionService = array();
	
	/**
	 * 存放所有Service的容器
	 * @var array
	 */
	private static $services = array();
	
	/**
	 * 获取Service反射对象的单例
	 * @param string $className 对象名称
	 * @return \ReflectionClass
	 */
	public static function refInstance($className) {
		if (isset(self::$reflectionService[$className])) {
			return self::$reflectionService[$className];
		}
		if (!\class_exists($className)) {
			ApiServerException::throwException("Service “{$className}” not found!", ApiServerException::ERROR_CODE_SERVICE_NOT_FOUND);
		}
		$reflectionClass = new \ReflectionClass($className);
		self::$reflectionService[$className] = $reflectionClass;
		return $reflectionClass;
	}
	
	/**
	 * 获取一个Service的实例
	 * @param string $serviceName service对应的类名
	 * @return multitype:|object
	 */
	public static function getService($serviceName) {
		if (isset(self::$services[$serviceName])) {
			return self::$services[$serviceName];
		}
		if (!\class_exists($serviceName)) {
			ApiServerException::throwException("Service “{$serviceName}” not found!", ApiServerException::ERROR_CODE_SERVICE_NOT_FOUND);
		}
		$service = self::refInstance($serviceName)->newInstance();
		self::$services[$serviceName] = $service;
		return $service;
	}
	
	
	/**
	 * 返回一个Model层的适配器
	 * @return \Wx\Wxserver\Compent\ModelDriver
	 */
	protected function modelDriver() {
		return new ModelDriver();
	}
}