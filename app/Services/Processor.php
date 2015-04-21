<?php

namespace App\Services;

use Cache;
use Carbon\Carbon;

class Processor {

	private $image;

	private $fileFactory;

	private $file;

	public function __construct(FileFactory $fileFactory)
	{
		$this->fileFactory = $fileFactory;
	}

	public function process($request)
	{
		if ($file = $this->getCached($request))
		{
			return $file;
		}

		$this->file = $this->fileFactory->make($request);

		$this->cache($request, $file);

		return $this->file;
	}

	public function make($file)
	{
		$file = $this->finder->find($file);

		$this->image = $this->imageManager->make($file);

		return $this;
	}

	function __call($name, $arguments)
	{
		$this->image = call_user_func_array([$this->imageManager, $name], $arguments);

		return $this;
	}

	private function getCached($request)
	{
		$key = $this->makeCacheKey($request->query());

		return Cache::get($key);
	}

	private function makeCacheKey($query)
	{
		$key = '';

		foreach($query as $name => $value)
		{
			$key .= "$name=$value&";
		}

		return $key;
	}

	private function cache($request, $file)
	{
		$key = $this->makeCacheKey($request->query());

		$expiresAt = Carbon::now()->addMinutes(env('CACHE_EXPIRING_MINUTES'));

		Cache::put($key, $file, $expiresAt);
	}

}
