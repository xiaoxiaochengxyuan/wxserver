<?php
use Wx\Wxserver\Compent\ApiServer;
use Wx\Wxserver\Compent\ApiServerException;
use Wx\Wxserver\Config\ApiConfig;
require_once __DIR__.'/vendor/autoload.php';
/*
 * 首先检查是否安装了swoole扩展,如果没有安装,那么提示错误
 */
if (!\extension_loaded('swoole')) {
	ApiServerException::throwException('has no swoole extension, please install!', ApiException::ERROR_CODE_SWOOLE_EXTENSION_IS_NOT_INSTALL);
}

if (count($argv) < 2) {
	die("Please input your run type(start|stop|restart|reload)\n");
}

$runType = $argv[1];

switch ($runType) {
	//运行服务器
	case 'start':
		if (ApiServer::isRun()) {
			echo "Api server is running,Can not run again!\n";
		} else {
			echo "启动服务器成功...\n";
			ApiServer::app()->run();
		}
		break;
	//关闭服务器
	case 'stop':
		if (!ApiServer::isRun()) {
			echo "Api Server is not running, Can not stop!\n";
		} else {
			echo "关闭Api服务器成功...\n";
			ApiServer::app()->stop();
		}
		break;
	case 'reload':
		if (!ApiServer::isRun()) {
			echo "Api Server is not running, Can not reload!\n";
		} else {
			echo "柔性重启Api服务器成功...\n";
			ApiServer::app()->reload();
		}
		break;
	case 'restart':
		if (!ApiServer::isRun()) {
			echo "Api Server is not running, Can not restart!\n";
		} else {
			ApiServer::app()->stop();
			sleep(2);
			echo "强制重启Api服务器成功...\n";
			ApiServer::app()->run();
		}
		break;
	default:
		echo '参数错误';
		break;
}