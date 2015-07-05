<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;

class File {

	private $request;

	private $valid = true;

	private $error;

	private $fileFinder;

	private $fileName;

	private $filesystem;

	private $url;

	private $image;

	private $urlHash;

	private $transformed = false;

	private $transformedFileName;

	public function __construct($request, FileFinder $fileFinder, Filesystem $filesystem, Image $image)
	{
		$this->request = $request;

		$this->fileFinder = $fileFinder;

		$this->filesystem = $filesystem;

		$this->image = $image;

		$this->parseRequest();
	}

	private function parseRequest()
	{
		if ( ! $this->url = $this->request->query->get('url'))
		{
			$this->valid = false;

			$this->error = 'URL not provided.';
		}

		$this->findFile();

		$this->processTransformations();
	}

	public function isValid()
	{
		return $this->valid;
	}

	public function getError()
	{
		return $this->error;
	}

	public function download()
	{
		$filetype = $this->filesystem->mimeType($this->getFinalFileName());

		$response = response()->make(file_get_contents($this->getRealFilename($this->getFinalFileName())), 200);

		$response->header('Content-Type', $filetype);

		$response->header("Content-Disposition", "filename=" . $this->getOriginalFileName());

		$response->setTtl(600);

		$response->expire(600);

		$response->setExpires(Carbon::now()->addDay(30));

		$response->setSharedMaxAge(600);

		return $response;
	}

	private function findFile()
	{
		$this->parseFileName($this->url);

		if ($this->fileFinder->find($this->transformedFileName = $this->getTransformedFileName()))
		{
			$this->transformed = true;
		}
		elseif ( ! $this->fileFinder->find($this->fileName))
		{
			$this->fetchOriginal();
		}
	}

	private function parseFileName($url)
	{
		$this->urlHash = SHA1($url);

		$extension = $this->getExtension($url);

		$path = $this->getBaseDir() . DIRECTORY_SEPARATOR . $this->makeDeepPath($this->urlHash);

		$this->fileName = $path . DIRECTORY_SEPARATOR . $this->urlHash . '.' . $extension;

		$this->makeTransformedFileName($path);

		$this->image->setFilename($this->getRealFilename());
	}

	private function makeDeepPath($string)
	{
		$path = '';

		for ($x = 0; $x <= min(8, strlen($string)); $x++)
		{
			$path .= ($path ? DIRECTORY_SEPARATOR : '') . $string[$x];
		}

		return $path;
	}

	private function getBaseDir()
	{
		return 'files';
	}

	private function fetchOriginal()
	{
		$contents = file_get_contents($this->url);

		$this->filesystem->put($this->fileName, $contents);
	}

	private function processTransformations()
	{
		if  ( ! $this->transformed)
		{
			foreach ($this->request->except('url') as $command => $value)
			{
				$this->image->transform($command, $value);

				$this->transformed = true;
			}

			if ($this->transformed)
			{
				$this->image->save($this->getRealFilename($this->getTransformedFileName()));
			}
		}
	}

	private function getTransformedFileName()
	{
		return $this->transformedFileName;
	}

	private function getExtension($fileName)
	{
		return pathinfo($fileName, PATHINFO_EXTENSION);
	}

	private function makeFileName($fileName)
	{
		return pathinfo($fileName, PATHINFO_FILENAME);
	}

	private function getRealFilename($fileName = null)
	{
		return $this
				->filesystem
				->getDriver()
				->getAdapter()
				->applyPathPrefix($fileName ?: $this->fileName);
	}

	private function getOriginalFileName()
	{
		return basename($this->url);
	}

	private function makeTransformedFileName($path)
	{
		$path = $path ? $path . DIRECTORY_SEPARATOR : $path;

		if ($this->transformedFileName)
		{
			return $this->transformedFileName;
		}

		$extension = $this->getExtension($this->fileName);

		$filename = $this->makeFileName($this->fileName);

		foreach ($this->request->except('url') as $key => $transformation)
		{
			$filename .= '_' . $key . '_' . $transformation;
		}

		return $this->transformedFileName = $path . str_slug($filename) . '.' . $extension;
	}

	private function getFinalFileName()
	{
		if ($this->transformed)
		{
			return $this->transformedFileName;
		}

		return $this->fileName;
	}

	public function setImage($image)
	{
		$this->image->setImage($image);
	}

	public function getImage()
	{
		return $this->image->getImage();
	}

	public function getFileName()
	{
		return $this->fileName;
	}

}
