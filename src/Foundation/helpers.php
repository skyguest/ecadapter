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

if ( !function_exists('real_ip')) {
    function real_ip() {
        static $realip = NULL;

        if ($realip !== NULL)
        {
            return $realip;
        }

        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip)
                {
                    $ip = trim($ip);

                    if ($ip != 'unknown')
                    {
                        $realip = $ip;

                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

        return $realip;
    }
}