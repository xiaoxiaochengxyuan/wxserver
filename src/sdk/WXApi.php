<?php
namespace Wx\Wxserver\Sdk;
/**
 * 客户端调用Api
 * @author xiawei
 */
class WXApi {
	/**
	 * 客户端要请求的Ip地址,请自己修改
	 * @var string
	 */
	private $ip = '127.0.0.1';
	/**
	 * 客户端请求的端口号,请自己修改
	 * @var string
	 */
	private $port = 12345;
	
	/**
	 * 应用名称,请自己修改
	 * @var string
	 */
	private $app = 'test';
	
	/**
	 * 用户名,请自己修改
	 * @var string
	 */
	private $username = 'test';
	
	/**
	 * 密码,请自己修改
	 * @var string
	 */
	private $password = 'test';
	/**
	 * WXApi的单例
	 * @var \Wx\Wxserver\Sdk\WXApi
	 */
	private static $WXAPI = null;
	
	/**
	 * 对应的Service加载器的容器
	 * @var array
	 */
	private static $WXSERVICES = array();
	
	/**
	 * 单例
	 * @return \Wx\Wxserver\Sdk\WXApi
	 */
	public static function instance() {
		if (empty(self::$WXAPI)) {
			self::$WXAPI = new self();
		}
		return self::$WXAPI;
	}
	
	/**
	 * 获取一个Service的加载器
	 * @param string $serviceName 对应的Service的名称
	 * @return WXService
	 */
	public function load($serviceName) {
		if (!isset(self::$WXSERVICES[$serviceName])) {
			self::$WXSERVICES[$serviceName] = new WXService($serviceName, $this);
		}
		return self::$WXSERVICES[$serviceName];
	}
	
	/**
	 * 获取Auth信息
	 * @return array
	 */
	public function getAuth() {
		return array(
			'app' => $this->app,
			'username' => $this->username,
			'password' =>  $this->password
		);
	}
	
	/**
	 * 获取Ip地址
	 * @return string
	 */
	public function getIp() {
		return $this->ip;
	}
	
	/**
	 * 获取端口
	 * @return string
	 */
	public function getPort() {
		return $this->port;
	}
}