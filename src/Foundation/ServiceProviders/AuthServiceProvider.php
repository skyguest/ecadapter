<?php
namespace Skyguest\Ecadapter\Foundation\ServiceProviders;

use ReflectionClass;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skyguest\Ecadapter\Support\ServiceProvider;
use Skyguest\Ecadapter\Auth\Auth;
use Skyguest\Ecadapter\Auth\EloquentUserProvider;

class AuthServiceProvider extends ServiceProvider {

	public function register(Container $pimple) {
		$pimple['auth'] = function ($pimple) {
			

			$user_model = $pimple['config']['auth.model'];
			$reflector = new ReflectionClass($user_model);
        	if (! $reflector->isInstantiable()) { 
        		throw new \Exception("auth_model can't reflect");
        	}
        	// 创建模型类
        	$user_model = $reflector->newInstance();

        	$provider = new EloquentUserProvider($pimple['config']['auth.model']);

        	$session = session();
        	$auth = new Auth($provider, $pimple, $session->driver());

			
			$data = $session->all();

			if ( empty($data) ) {
				return $auth;
			}

			$user_id = $data['user_id'];
			if ( empty($user_id) ) {
				return $auth;
			}

        	$user = $user_model->find($user_id);

        	if ( $user ) {
        		$auth->setUser($user);
        	}

        	return $auth;
		};
	}
}