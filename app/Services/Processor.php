<?php

namespace App\Services;

class Processor
{
	private $image;

	private $file;

	private $response;

	public function __construct(File $file, Cache $cache)
	{
		$this->file = $file;

		$this->cache = $cache;
	}

	public function process($request)
	{
		if ($image = $this->cache->get($request))
		{
			return $image;
		}

		$this->file->processRequest($request);

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
		return $this->response->make(
			[
				'success' => false,
				'error' => $this->file->getError(),
			]
		);
	}

	public function setResponse($response)
	{
		$this->response = $response;

		$this->file->setResponse($response);

		return $this;
	}
}
