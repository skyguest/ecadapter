<?php
namespace Skyguest\Ecadapter\Support;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

abstract class ServiceProvider implements ServiceProviderInterface {
	protected $boot = false;

	public function needBoot() {
		return $this->boot;
	}

	public function boot(Container $pimple) {

	}
}