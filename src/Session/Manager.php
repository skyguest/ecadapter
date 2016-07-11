<?php
namespace Skyguest\Ecadapter\Session;

use Illuminate\Support\Manager as LaravelManager;
use SessionHandlerInterface;

class Manager extends LaravelManager {
	
	// 适配ECSHOP的SESSION
	public function createEcshopDriver() {
		return $this->buildSession(new DatabaseSessionHandler($this->app['db'], $this->app['config']['session'], $this->app));
	}

	protected function buildSession(SessionHandlerInterface $handler) {
		return new Store($this->app['config']['session.cookie'], $handler);
	}

	public function getDefaultDriver() {
		return $this->app['config']['session.driver'] ?: 'ecshop';
	}
}