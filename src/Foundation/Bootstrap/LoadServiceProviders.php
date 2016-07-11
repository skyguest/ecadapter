<?php
namespace Skyguest\Ecadapter\Foundation\Bootstrap;

use Skyguest\Ecadapter\Foundation\Application;

class LoadServiceProviders {
	
	public function bootstrap(Application $app) {

		$providers = $app['config']['app.providers'];

		foreach ($providers as $key => $provider) {
			$service = new $provider();
			$app->register($service);
			if ( $service->needBoot() ) {
				// 以后再完善
			}
		}
	}
}
