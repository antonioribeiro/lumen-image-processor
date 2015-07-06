<?php

namespace App\Services;

use Carbon\Carbon;

class Cache
{
	/**
	 * @var \Illuminate\Cache\CacheManager
	 */
	private $cache;

	public function __construct()
	{
		$this->cache = app('cache');
	}

	public function get($request)
	{
		return $this->cache->get($this->makeCacheKey($request));
	}

	public function put($request, $file)
	{
		if ($file)
		{
			$this->cache->put(
				$this->makeCacheKey($request),
				$file,
				Carbon::now()->addMinutes(env('CACHE_EXPIRE_MINUTES'))
			);
		}

		return $this;
	}

	private function makeCacheKey($query)
	{
		$key = '';

		foreach($query->query() as $name => $value)
		{
			$key .= "$name=$value&";
		}

		return $key;
	}
}
