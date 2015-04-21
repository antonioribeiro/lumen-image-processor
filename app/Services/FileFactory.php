<?php

namespace App\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\FileStore;

class FileFactory {

	public function make($request)
	{
		$cache = new CacheManager($app = app());

//		$cache->setDefaultDriver('Illuminate\Cache\FileStore');
//
//		$store = new FileStore($app['files'], '/tmp');
//
//		$cache->store()
//
//		$app->singleton('cache.store', function($cache)
//		{
//			return $cache->driver();
//		});

		return $app->make('App\Services\File', [$request, $cache]);
	}

}
