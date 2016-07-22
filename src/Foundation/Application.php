<?php

namespace Skyguest\Ecadapter\Foundation;
use Pimple\Container;
use Symfony\Component\Debug\Debug;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container as LaravelContainer;

class Application extends Container {

	protected static $instance;

	protected $providers = [
		ServiceProviders\LogServiceProvider::class,
		ServiceProviders\DBServiceProvider::class,
		ServiceProviders\SessionServiceProvider::class,
		ServiceProviders\AuthServiceProvider::class,
        ServiceProviders\ViewServiceProvider::class,
	];

	protected $isboot = false;

    protected $config_path;

	/**
	 * 初始化
	 * @author Young <ybys@qq.com>
	 * @date   2016-07-08T10:24:17+0800
	 * @param  [type]                   $config [description]
	 */
	public function __construct($config) {
		parent::__construct();
		$this['config'] = function() use ($config) {
			return new Config($config);
		};

		if ( $this['config']['debug'] ) {
            if ( class_exists(Debug::class)) {
                Debug::enable();
            } else {
                ini_set('display_errors', 'on');
                error_reporting(E_ALL);
            }
		}

		$this->regiestBase();
		$this->regiestProviders();

	}

	private function regiestBase() {
		static::setInstance($this);
		$this['app'] = function () {
			return static::getInstance();
		};
        $this['container'] = function () {
            return new LaravelContainer;
        };
        $this['events'] = function () {
            return new Dispatcher($this['container']);
        };
        $this['core'] = function () {
            return new Core();
        };
	}

	private function regiestProviders() {
		foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
	}

    public static function setInstance(Application $container) {
        static::$instance = $container;
    }

	public static function getInstance() {
        return static::$instance;
    }

    // 获取是否启动过
    public function hasBooted() {
    	return $this->isboot;
    }

    // 设置启动注册
    public function bootstrapWith(array $bootstraps) {
    	$this->isboot = true;

    	foreach ($bootstraps as $bootstrap) {
    		(new $bootstrap())->bootstrap($this);
    	}
    }

    public function setConfigPath($path) {
        $this->config_path = $path;
    }

	/**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    public function url($url) {
        return $url;
    }

    public function rev($file, $manifestFile = null, $fullpath = false) {
        return $file;
    }
}