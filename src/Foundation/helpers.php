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
			$id = $_COOKIE[$session->getName()];
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
