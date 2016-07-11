<?php
namespace Skyguest\Ecadapter\Foundation\ServiceProviders;

use Pimple\Container;
use Skyguest\Ecadapter\Support\ServiceProvider;

use Illuminate\Filesystem\Filesystem;

use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container as LaravelContainer;

class ViewServiceProvider extends ServiceProvider {
	
	public function register(Container $pimple) {

		$pimple['files'] = function () {
			return new Filesystem;
		};

		$pimple['view.engine.resolver'] = function () use ($pimple) {
			$resolver = new EngineResolver;

            foreach (['php', 'blade'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver, $pimple);
            }

            return $resolver;
		};

		$pimple['view.finder'] = function () use ($pimple) {
            $paths = $pimple['config']['view.paths'];

            return new FileViewFinder($pimple['files'], $paths);
        };

		$pimple['view'] = function () use ($pimple) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $pimple['view.engine.resolver'];

            $finder = $pimple['view.finder'];

            $env = new Factory($resolver, $finder, $pimple['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $env->setContainer($pimple['container']);

            $env->share('app', $pimple);

            return $env;
        };
	}


    public function registerPhpEngine($resolver, $pimple)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    public function registerBladeEngine($resolver, $pimple)
    {
    	$pimple['blade.compiler'] = function() use ($pimple) {
    		$cache = $pimple['config']['view.compiled'];

    		return new BladeCompiler($pimple['files'], $cache);
    	};

        $resolver->register('blade', function () use ($pimple) {
        	return new CompilerEngine($pimple['blade.compiler']);
        });
    }
}