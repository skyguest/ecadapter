<?php

use Skyguest\Ecadapter\Foundation\Application;

if ( !function_exists('app') ) {
	function app($name = null) {
		$app = Application::getInstance();

		if (empty($name)) {
            return $app;
        }

        return ($app && is_string($name)) ? $app->$name : null;
	}
}

if ( !function_exists('session') ) {
	function session($key = null, $default = null) {
		$session = app('session');
		if ( !$session->isStarted() ) {
            if ( isset($_COOKIE[$session->getName()]) ) {
                $id = $_COOKIE[$session->getName()];
            } else {
                $id = $session->generateSessionId();
                $config = app('config')['session'];
                setcookie($session->getName(), $id, time()+86400*7, $config['cookie_path'], $config['cookie_domain'], $config['session_cookie_secure']);
            }
			$session->setId($id);
			$session->start();
		}
		
		if ( empty($key) ) {
			return $session;
		}

		if (is_array($key)) {
            return $session->put($key);
        }

        return $session->get($key, $default);
	}
}

if ( !function_exists('is_in_ecshop') ) {
	function is_in_ecshop() {
		return defined('ROOT_PATH');
	}
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('bcrypt')) {
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    function bcrypt($value, $options = [])
    {
        return app('hash')->make($value, $options);
    }
}

if ( !function_exists('rev') ) {
    function rev($file, $manifestFile = null, $fullpath = false) {
        return app()->rev($file, $manifestFile, $fullpath);
    }
}

if ( !function_exists('url') ) {
    function url($url, $param = []) {
        return app()->url($url, $param);
    }
}

if ( !function_exists('real_ip')) {
    function real_ip() {
        return app('request')->ip();
    }
}