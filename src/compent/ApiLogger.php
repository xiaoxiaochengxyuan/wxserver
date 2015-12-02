<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Config\ApiConfig;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
/**
 * ApiLogger
 * @author xiawei
 */
class ApiLogger extends Logger{
	private $logger = null;
	public function __construct() {
		$logConfig = ApiConfig::getLogConfig();
		parent::__construct($logConfig['name']);
		parent::pushHandler(new RotatingFileHandler("{$logConfig['log_path']}/info-log"), Logger::INFO);
		parent::pushHandler(new RotatingFileHandler("{$logConfig['log_path']}/info-log"), Logger::ERROR);
	}
	/**
	 * 获取一个ApiLogger的实列
	 * @return \Wx\Wxserver\Compent\ApiLogger
	 */
	public static function instance() {
		return new self();
	}
}