<?php
namespace Wx\Wxserver\Config;
use Wx\Wxutils\StringUtil;
/**
 * Api的配置对象
 * @author xiawei
 */
class ApiConfig {
	//这里填写cpu的个数,开发环境请自行修改
	private static $cpuNum = 2;
	//加密和解密传输字符串对应的key
	const ENCRYTP_DECRYPT_SALT = '8972cd270e4b3d788efd8ed4e38d9eb62b7c6907';
	
	/**
	 * 返回swoole服务器配置文件对应的数组
	 * @return multitype:
	 */
	public static function swServerConfig() {
		return array(
			'ip' => '127.0.0.1',
			'port' => '12345',
			//此参数来调节主进程内事件处理线程的数量，以充分利用多核,一般设置为CPU核数的1-4倍,最大不能超过self::$cpuNum * 4
			'reactor_num' => self::$cpuNum * 2,
			//工作进程数,这里默认设置为cpu数量的2倍,一般是cpu数量的1到4倍,每个进程占用40M内存,这里请合理调节
			'worker_num' => self::$cpuNum * 2,
			
			//这里设置允许最大的连接数,默认是512,请记住这个值不能超过ulimit -n的的值
			'max_conn' => 512,
			//这里配置dispatch_mode采用抢占模式,主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker,这种模式非常适合Api服务器
			'dispatch_mode' => 3,
			//这里设置服务为守护进程,在后台自动运行
			'daemonize' => 1,
			//设置没30秒发送一次心跳检测
			'heartbeat_check_interval' => 30,
			//设置允许最大的空闲连接
			'heartbeat_idle_time' => 600,
			//开启cpu亲和测试,这样可以减少cpu切换的代价,提高命中率
			'open_cpu_affinity' => 1,
		);
	}
	
	/**
	 * 获取App的配置信息
	 * @return array
	 */
	public static function appsConfig() {
		return array(
			'test' => array(
				'username' => 'test',
				'password' => 'test',
			)
		);
	}
	
	
	/**
	 * 现阶段Api支持一主多从的模式
	 */
	public static function getDBConfig() {
		//下面是主从数据库的配置方法
		return array(
			//主库的配置
			/*'write' => array(
				'dns' => 'mysql:host=127.0.0.1;dbname=test',
				'username' => 'root',
				'password' => '398062080',
				'charset' => 'utf8',
			),
			//从库的配置
			'read' => array(
				array(
					'dns' => 'mysql:host=127.0.0.1;dbname=test',
					'username' => 'root',
					'password' => '398062080',
					'charset' => 'utf8',
				),
			),*/
			//是否使用数据库连接池
			'pool' => false,
			'db' => array(
				'dns' => 'mysql:host=127.0.0.1;dbname=test',
				'username' => 'root',
				'password' => '398062080',
				'charset' => 'utf8',
			),
		);
	}
	
	/**
	 * 获取Log相关的配置
	 * @return multitype:string
	 */
	public static function getLogConfig() {
		return array(
			'log_path' => '/data/log/wxserver',
			'name' => 'wxserver_log',
		);
	}
}