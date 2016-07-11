<?php
namespace Skyguest\Ecadapter\Support;

use Pimple\ServiceProviderInterface;

abstract class ServiceProvider implements ServiceProviderInterface {
	protected $boot = false;

	public function needBoot() {
		return $this->boot;
	}
}