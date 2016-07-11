<?php
namespace Skyguest\Ecadapter\Foundation;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

class Core {

	protected $app;

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

	public function run($group = null, $controller = null, $function = null) {
		$this->handle();

		$request = Request::createFromGlobals();

		// ======== 开始调度，设置调度那个控制器===============
		$group = $group ?: 'Web';
		$controller = $controller ?: $request->get('c', 'DefaultController');
		$function = $function ?: $request->get('m', 'index');
		$class = '\\App\\Controllers\\'.$group.'\\'.ucfirst($controller);
		$load_class = $class . "::" . $function;

		if ( !class_exists($class) ) {
			$load_class = '\\App\\Controllers\\'.$group.'\\NoFoundController::index';
		}

		// ====================================================
		$dispatcher = new EventDispatcher();

		// 设置视图回调
		$dispatcher->addListener(KernelEvents::VIEW, function ($event) {
		    $event->setResponse(new Response($event->getControllerResult()));
		});

		$controllerResolver = new ControllerResolver();
		$argumentResolver = new ArgumentResolver();

		$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

		$request->attributes->set('_controller', $load_class);

		$response = $kernel->handle($request);
		$response->send();

		$kernel->terminate($request, $response);
	}
}