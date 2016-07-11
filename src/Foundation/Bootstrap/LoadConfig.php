<?php
namespace Skyguest\Ecadapter\Foundation\Bootstrap;

use Skyguest\Ecadapter\Foundation\Application;

class LoadConfig {
	
	public function bootstrap(Application $app) {

		$config_path = $app['config']['config_path'];

		$app->setConfigPath($config_path);

		if ( file_exists($file = $config_path . DIRECTORY_SEPARATOR . 'app.php') ) {
			$app['config']->set('app', require $file);
		} else {
			$app['config']->set('app', []);
		}
	}
}