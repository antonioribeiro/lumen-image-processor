<?php

namespace App\Services;

use Carbon\Carbon;
use Cache as LaravelCache;

class Cache {

	public function get($request)
	{
		return LaravelCache::get($this->makeCacheKey($request));
	}

	public function put($request, $file)
	{
		if ($file)
		{
			LaravelCache::put(
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
