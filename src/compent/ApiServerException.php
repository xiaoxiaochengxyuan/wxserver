<?php
namespace Wx\Wxserver\Compent;
use Wx\Wxserver\Compent;
/**
 * Api服务器异常对象
 * @author xiawei
 */
class ApiServerException extends \Exception {
	//定义swoole没有安装的异常代码
	const ERROR_CODE_SWOOLE_EXTENSION_IS_NOT_INSTALL = 50050;
	//无法启动Api服务器异常代码
	const ERROR_CODE_CAN_NOT_RUN_API_SERVER = 50051;
	//Api服务器没有没有启动的错误代码
	const ERROR_CODE_API_SERVER_IS_NOT_RUNNING = 50052;
	//找不到Service的错误代码
	const ERROR_CODE_SERVICE_NOT_FOUND = 50053;
	//数据库方面的错误代码
	const ERROR_CODE_DB_ERROR = 50054;
	public function __construct($message, $code, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
	public static function throwException($message, $code, $previous = null) {
		throw new self($message, $code, $previous);
	}
}