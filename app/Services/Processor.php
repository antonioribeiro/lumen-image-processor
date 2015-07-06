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
			return $image;
		}

		$this->file = $this->fileFactory->make($request);

		if ( ! $this->file->isValid())
		{
			return $this->makeResponseForInvalidFile();
		}

		$image = $this->file->download();

		$this->cache->put($request, $image);

		return $image;
	}

	function __call($name, $arguments)
	{
		$this->image = call_user_func_array([$this->imageManager, $name], $arguments);

		return $this;
	}

	private function makeResponseForInvalidFile()
	{
		return response(
			[
				'success' => false,
				'error' => $this->file->getError(),
			]
		);
	}

}
