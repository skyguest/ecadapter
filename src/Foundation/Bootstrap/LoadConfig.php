<?php
namespace Skyguest\Ecadapter\Foundation\Bootstrap;

use Skyguest\Ecadapter\Foundation\Application;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LoadConfig {
	
	public function bootstrap(Application $app) {

		$config_path = $app['config']['config_path'];

		$app->setConfigPath($config_path);

		foreach ($this->getConfigurationFiles($config_path) as $key => $path) {
            $app['config']->set($key, require $path);
        }
	}

	protected function getConfigurationFiles($config_path)
    {
        $files = [];

        $configPath = realpath($config_path);

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $nesting = $this->getConfigurationNesting($file, $configPath);

            $files[$nesting.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    protected function getConfigurationNesting(SplFileInfo $file, $configPath)
    {
        $directory = dirname($file->getRealPath());

        if ($tree = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';
        }

        return $tree;
    }
}