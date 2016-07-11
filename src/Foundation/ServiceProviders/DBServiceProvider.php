<?php
namespace Skyguest\Ecadapter\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skyguest\Ecadapter\Support\ServiceProvider;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container as LaravelContainer;

class DBServiceProvider extends ServiceProvider {
	
	public function register(Container $pimple) {
		$pimple['db'] = function ( $pimple ) {
			$db = new Manager;

			$db->addConnection([
			    'driver'    => 'mysql',
			    'host'      => $pimple['config']['db.host'],
			    'database'  => $pimple['config']['db.database'],
			    'username'  => $pimple['config']['db.username'],
			    'password'  => $pimple['config']['db.password'],
			    'charset'   => 'utf8',
			    'collation' => 'utf8_unicode_ci',
			    'prefix'    => $pimple['config']['db.prefix'],
			]);

			$db->setEventDispatcher($pimple['events']);

			// Make this Capsule instance available globally via static methods... (optional)
			$db->setAsGlobal();

			// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
			$db->bootEloquent();

			if ( $pimple['config']['log.sql']) {
				// 记录查询过的日志
				$manager = $db->getDatabaseManager();
				try {
	                $manager->listen(
	                    function ($query, $bindings = null, $time = null, $connectionName = null) use ($manager, $pimple) {
	                        // Laravel 5.2 changed the way some core events worked. We must account for
	                        // the first argument being an "event object", where arguments are passed
	                        // via object properties, instead of individual arguments.
	                        if ( $query instanceof \Illuminate\Database\Events\QueryExecuted ) {
	                            $bindings = $query->bindings;
	                            $time = $query->time;
	                            $connection = $query->connection;

	                            $query = $query->sql;
	                        } else {
	                            $connection = $manager->connection($connectionName);
	                        }

	                        $pimple['log']->debug($query, $bindings);
	                        $pimple['sql_log']->set($query, $bindings);
	                    }
	                );
	            } catch (\Exception $e) {
	                $pimple['log']->debug($e->getMessage());
	            }
			}

			return $db;
		};
	}
}