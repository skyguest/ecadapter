# Ecadapter

**Ecadapter**是一个小框架，为了兼容老的ecshop，过渡期而写的。
 
- **功能丰富** ：支持加载服务，扩展方便；
- **兼容会话** ：Session调用原系统，集成可以保持登录；

-------------------


## Ecadapter简介

> 持续开发，摒弃过时的    —— [开发者](http://www.shangxiaxing.com)


### 代码块，小框架入口
```php
if ( !defined('EC_CHARSET') ) {
	require __DIR__.'/../data/config.php';
	date_default_timezone_set($timezone);
}
$_app = new Skyguest\Ecadapter\Foundation\Application([
    // 调试模式
	'debug' => true,
	'db' => [
		'host' => $db_host,
		'database' => $db_name,
		'username' => $db_user,
		'password' => $db_pass,
		'prefix' => $prefix,
	],
	'log' => [
		'file' => dirname(__DIR__) . '/temp/applogs/' .date('Y/m/d/'). 'app.log',
		'level' => 'debug',
		'sql' => true,
	],
	'session' => [
		'cookie' => 'ECS_ID',
		'lifetime' => 1800,
		'cookie_path' => $cookie_path,
		'cookie_domain' => $cookie_domain,
		'session_cookie_secure' => false,
	],
	'auth' => [
		'model' => App\Models\Users::class,
	],
	// 配置的路径
	'config_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config',
	// 试图配置
	'view' => [
		'paths' => [
			realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'View')
		],
		'compiled' => realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'viewtemp'),
	],
]);

// 初始化数据库
$_db = $_app->db;
```


## 反馈与建议
- 邮箱：<ybys@qq.com>

---------
感谢阅读这份帮助文档。
