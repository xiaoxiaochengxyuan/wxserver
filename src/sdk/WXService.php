<?php
namespace Wx\Wxserver\Sdk;
use Wx\Wxutils\StringUtil;
/**
 * Service客户端加载类
 * @author xiawei
 */
class WXService {
	/**
	 * 名称
	 * @var string
	 */
	private $name = null;
	
	/**
	 * Api的实列
	 * @var WXApi
	 */
	private $api = null;
	
	private $encrypt_decrypt_salt = '8972cd270e4b3d788efd8ed4e38d9eb62b7c6907';
	/**
	 * 构造方法
	 * @param string $name
	 */
	public function __construct($name, WXApi $api) {
		$this->name = $name;
		$this->api = $api;
	}
	
	public function __call($methodName, $arguments) {
		$auth = $this->api->getAuth();
		$sendData = \array_merge(array('type' => 'api-call', 'params' => $arguments, 'service' => $this->name, 'method' => $methodName), $auth);
		$sendJson = \json_encode($sendData);
		$sendStr = $this->encryStr($sendJson, $this->encrypt_decrypt_salt);
		$client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
		$client->connect($this->api->getIp(), $this->api->getPort()) or die('连接服务器失败');
		$client->send($sendStr);
		$result = $client->recv();
		$client->close();
		$resultJson = $this->decryStr($result, $this->encrypt_decrypt_salt);
		$resultArr = \json_decode($resultJson, true);
		if ($resultArr['succ']) {
			return $resultArr['data'];
		} else {
			throw new \Exception($resultArr['msg'], 50100, null);
		}
	}
	
	/**
	 * 对一个字符串进行可逆加密操作
	 * @param string $str 要加密的字符串
	 * @param string $key 用于加密的Key
	 * @return string 加密后的字符串
	 */
	public function encryStr($str, $key='5BAB6FAC-4283-4ebe-AE97-3CBCA9CA70B0') {
		return base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $str, MCRYPT_MODE_ECB));
	}
	
	/**
	 * 对一个字符串进行解密操作
	 * @param string $str 要解密的字符串
	 * @param string $key 用于解密的key
	 * @return string 返回解密之后的字符串
	 */
	public function decryStr($str,$key='5BAB6FAC-4283-4ebe-AE97-3CBCA9CA70B0') {
		return trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, base64_decode($str), MCRYPT_MODE_ECB));
	}
}