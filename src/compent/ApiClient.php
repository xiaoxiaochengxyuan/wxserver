<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Wx\Wxutils\StringUtil;
/**
 * ApiServer的客户端调用对象
 * @author xiawei
 */
class ApiClient {
	/**
	 * 要连接的Ip地址
	 * @var string
	 */
	private $ip;
	/**
	 * 要连接的端口
	 * @var integer
	 */
	private $port;
	/**
	 * 超时时间
	 * @var integer
	 */
	private $timeout;
	
	/**
	 * swoole客户端
	 * @var \swoole_client
	 */
	private $client;
	
	/**
	 * Api客户端
	 * @var ApiClient
	 */
	private static $APICLIENT = null;
	private function __construct($ip, $port, $timeout = -1) {
		$this->ip = $ip;
		$this->port = $port;
		$this->timeout = $timeout;
	}
	
	/**
	 * 获取ApiClient的单例
	 * @return \Wx\Wxserver\Compent\ApiClient
	 */
	public static function instance() {
		if (empty(self::$APICLIENT)) {
			$swServerConfig = ApiConfig::swServerConfig();
			self::$APICLIENT = new self($swServerConfig['ip'], $swServerConfig['port']);
		}
		return self::$APICLIENT;
	}
	
	/**
	 * 发送数据
	 * @param string $data
	 * @return unknown
	 */
	public function sendData($data) {
		if (empty($this->client)) {
			$this->client = new \swoole_client(SWOOLE_SOCK_TCP);
		}
		if (!$this->client->connect($this->ip, $this->port, -1)) {
			exit("connect failed. Error: {$this->client->errCode}\n");
		}
		if (\is_array($data) || \is_object($data)) {
			$data = \json_encode($data);
		}
		$data = StringUtil::encryStr($data, ApiConfig::ENCRYTP_DECRYPT_SALT);
		$this->client->send($data);
		$result = $this->client->recv();
		return StringUtil::decryStr($result, ApiConfig::ENCRYTP_DECRYPT_SALT);
	}
	
	/**
	 * 析构方法
	 */
	public function __destruct() {
		if (!empty($this->client)) {
			$this->client->close();
		}
	}
}