<?php
namespace Skyguest\Ecadapter\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skyguest\Ecadapter\Support\ServiceProvider;
use Skyguest\Ecadapter\Session\Manager;

class SessionServiceProvider extends ServiceProvider {

	public function register(Container $pimple) {
        $pimple['session'] = function ($pimple) {
        	return new Manager($pimple);
        };
	}
}