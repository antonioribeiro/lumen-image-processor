<?php

namespace App\Services;

class Processor {

	private $image;

	private $fileFactory;

	private $file;

	public function __construct(FileFactory $fileFactory, Cache $cache)
	{
		$this->fileFactory = $fileFactory;

		$this->cache = $cache;
	}

	public function process($request)
	{
		if ($image = $this->cache->get($request))
		{
			$this->file = app()->make('App\Services\File', [$request]);

			$this->file->setImage($image);

			return $this->file;
		}

		$this->file = $this->fileFactory->make($request);

		$this->cache->put($request, $this->file->getImage());

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

}
