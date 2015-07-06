<?php

namespace App\Services;

class Processor
{
	private $file;

	private $response;

	private $cache;

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

		$response = $this->file->getResponse();

		$this->cache->put($request, $response);

		return $response;
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
