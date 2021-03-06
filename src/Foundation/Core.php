<?php
namespace Skyguest\Ecadapter\Foundation;

use Symfony\Component\EventDispatcher\EventDispatcher;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

use Skyguest\Ecadapter\Http\Request;
use Skyguest\Ecadapter\Http\HttpResponseException;

use Illuminate\Contracts\View\View;

use Symfony\Component\Debug\Debug;

class Core {

	protected $app;

	protected $filename = 'skyguest.php';

	private $error_handler;

	protected $bootstrappers = [
        'Skyguest\Ecadapter\Foundation\Bootstrap\LoadConfig',
        'Skyguest\Ecadapter\Foundation\Bootstrap\LoadServiceProviders',
    ];
	
	public function __construct(Application $app = null) {
		$this->app = $app ?: app();
	}

	public function handle() {
		$this->bootstrap();
	}

	public function bootstrap() {
		if ( !$this->app->hasBooted() ) {
			$this->app->bootstrapWith($this->getBootstrappers());
		}
	}

	public function getBootstrappers() {
		return $this->bootstrappers;
	}

	public function getFileName() {
		return $this->filename;
	}

	public function run($group = null, $controller = null, $function = null) {
		// 注册框架运行的服务.
		$this->handle();

		// 请求参数
		$request = Request::capture();

		// 设置请求
		$this->app['request'] = $request;


		// 入口文件
		$baseUrl = $request->getBaseUrl();
		$this->filename = empty($baseUrl) ? $request->getSchemeAndHttpHost() . $request->server('SCRIPT_FILENAME') : $request->getSchemeAndHttpHost() . $baseUrl;

		

		if ( $path_info = trim($request->getPathInfo(),'/') ) {
			$args = explode('/', $path_info);
			define('BY_PATH_INFO', true);
			if ( count($args) == 1 ) {
				$controller = $controller ?: $args[0];
			} elseif ( count($args) >= 2 ) {
				$controller = $controller ?: $args[0];
				$function = $function ?: $args[1];
			}
		}

		// ======== 开始调度，设置调度那个控制器===============
		$group = ucfirst($group) ?: 'Web';
		$controller = $controller ?: $request->get('c', 'DefaultController');
		$controller = strrpos($controller, 'Controller') !== false ? $controller : $controller . 'Controller';
		$function = $function ?: $request->get('m', 'index');
		$class = '\\App\\Controllers\\'.$group.'\\'.ucfirst($controller);
		$load_class = $class . "::" . $function;
		// ======== 基础控制器设置结束 ========================

		// 检查控制器类存在不
		if ( !class_exists($class) ) {
			$class = '\\App\\Controllers\\'.$group.'\\NoFoundController';
			$function = 'index';
			$load_class = $class . '::' . $function;
		}

		// 如果没有找到类方法，设置为404
		if ( !is_callable([$class, $function]) ) {
			$class = NoFoundController::class;
			$function = 'index';
			$load_class = NoFoundController::class . '::' . $function;
		}

		// 事件调度器
		$dispatcher = new EventDispatcher();

		// 调度控制器参数事件
		$dispatcher->addListener(KernelEvents::CONTROLLER_ARGUMENTS, function ($event) {
			// 获取调度的参数
			$arguments = $event->getArguments();
			// 获取调度的控制器
			$controller = $event->getController();
			// 获取类
			$class = $controller[0];
			// 获取方法
			$function = $controller[1];
			// 检查调度方法在不在,在的话转发到调度方法去
			if ( method_exists($class, '_do_action') ) {
				$controller = [$class, '_do_action'];
				// 将方法设置为第一个参数
				array_unshift($arguments, $function);

				// 重新调度
				$event->setController($controller);
		    	$event->setArguments($arguments);
			}
		});

		// 设置视图事件回调
		$dispatcher->addListener(KernelEvents::VIEW, function ($event) {
			$result = $event->getControllerResult();
			// 重置视图的错误报告，忽略掉NOTICE的，视图太多NOTIC了
			$error_report = error_reporting();
			error_reporting(E_ALL ^ E_NOTICE);
		    $event->setResponse(new Response( ($result instanceof View) ? $result->render() : $result ));
		    // 恢复原来的错误报告
		    error_reporting($error_report);
		});

		// 设置异常回调
		$dispatcher->addListener(KernelEvents::EXCEPTION, function($event) {
			// 获取异常
			$e = $event->getException();
			// 如果是自定义异常
			if ( $e instanceof HttpResponseException) {
				// 设置成正常响应
				$event->setResponse( $e->getResponse() );
			}
		});


		// 以下是Symfony默认HTTP调度，无需修改
		$controllerResolver = new ControllerResolver();
		$argumentResolver = new ArgumentResolver();

		$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

		$request->attributes->set('_controller', $load_class);

		$response = $kernel->handle($request);
		$response->send();

		$kernel->terminate($request, $response);
	}

	public function handle_error($code, $message, $file = '', $line = 0, $context = array()) {

        if ( $code == E_NOTICE) {
        	return true;
        }

        if ($this->error_handler) {
            return call_user_func($this->error_handler, $code, $message, $file, $line, $context);
        }
	}
}