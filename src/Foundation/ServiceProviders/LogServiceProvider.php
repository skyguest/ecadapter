<?php
namespace Skyguest\Ecadapter\Foundation\ServiceProviders;

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skyguest\Ecadapter\Support\ServiceProvider;
use Skyguest\Ecadapter\DataBase\SqlLog;

class LogServiceProvider extends ServiceProvider {
	
	public function register(Container $pimple) {
		$pimple['log'] = function ($pimple) {

			$logger = new Logger('ecadapter');
	        if (!$pimple['config']['debug'] || defined('PHPUNIT_RUNNING')) {
	            $logger->pushHandler(new NullHandler());
	        } elseif ($logFile = $pimple['config']['log.file']) {
	            $logger->pushHandler(new StreamHandler($logFile, $pimple['config']->get('log.level', Logger::WARNING)));
	        }
            return $logger;
        };

        $pimple['sql_log'] = function ($pimple) {
        	return new SqlLog();
        };
	}
}