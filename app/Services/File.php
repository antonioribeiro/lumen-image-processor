<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;

class File
{
	private $request;

	private $valid = true;

	private $error;

	private $fileFinder;

	private $fileName;

	private $filesystem;

	private $url;

	private $image;

	private $urlHash;

	private $wasTransformed = false;

	private $transformedFileName;

	private $response;

	const URL_PARAMETER = 'url';

	const FILES_FOLDER = 'files';

	const TRANSFORMATION_SEPARATOR = '_';

	const SLUG_SEPARATOR = '.';

	const ERROR_SLUG_NOT_PROVIDED = 'URL not provided.';

	const REQUEST_TTL = 600;

	const REQUEST_EXPIRING_DAYS = 30;

	const PATH_DEPTH = 8;

	public function __construct(FileFinder $fileFinder, Filesystem $filesystem, Image $image)
	{
		$this->fileFinder = $fileFinder;

		$this->filesystem = $filesystem;

		$this->image = $image;
	}

	public function processRequest($request)
	{
		$this->request = $request;

		$this->parseRequest();
	}

	private function parseRequest()
	{
		if ( ! $this->url = $this->request->query->get(self::URL_PARAMETER))
		{
			$this->valid = false;

			$this->error = self::ERROR_SLUG_NOT_PROVIDED;
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

	public function getResponse()
	{
		$filetype = $this->filesystem->mimeType($this->getFinalFileName());

		$response = $this->response->make(file_get_contents($this->getRealFilename($this->getFinalFileName())), 200);

		$response->header('Content-Type', $filetype);

		$response->header("Content-Disposition", "filename=" . $this->getOriginalFileName());

		$response->setTtl(self::REQUEST_TTL);

		$response->expire(self::REQUEST_TTL);

		$response->setExpires(Carbon::now()->addDay(self::REQUEST_EXPIRING_DAYS));

		$response->setSharedMaxAge(self::REQUEST_TTL);

		return $response;
	}

	private function findFile()
	{
		$this->parseFileName($this->url);

		if ($this->fileFinder->find($this->transformedFileName = $this->getTransformedFileName()))
		{
			$this->wasTransformed = true;
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

		$this->fileName = $path . DIRECTORY_SEPARATOR . $this->urlHash . self::SLUG_SEPARATOR . $extension;

		$this->makeTransformedFileName($path);

		$this->image->setFilename($this->getRealFilename());
	}

	private function makeDeepPath($string)
	{
		$path = '';

		for ($x = 0; $x <= min(self::PATH_DEPTH, strlen($string)); $x++)
		{
			$path .= ($path ? DIRECTORY_SEPARATOR : '') . $string[$x];
		}

		return $path;
	}

	private function getBaseDir()
	{
		return env('STORAGE_FILES_DIR', self::FILES_FOLDER);
	}

	private function fetchOriginal()
	{
		$contents = file_get_contents($this->url);

		$this->filesystem->put($this->fileName, $contents);
	}

	private function processTransformations()
	{
		if  ( ! $this->wasTransformed)
		{
			foreach ($this->request->except(self::URL_PARAMETER) as $command => $value)
			{
				$this->image->transform($command, $value);

				$this->wasTransformed = true;
			}

			if ($this->wasTransformed)
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

		foreach ($this->request->except(self::URL_PARAMETER) as $key => $transformation)
		{
			$filename .= self::TRANSFORMATION_SEPARATOR . $key . self::TRANSFORMATION_SEPARATOR . $transformation;
		}

		return $this->transformedFileName = $path . str_slug($filename) . self::SLUG_SEPARATOR . $extension;
	}

	private function getFinalFileName()
	{
		if ($this->wasTransformed)
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

	public function setResponse($response)
	{
		$this->response = $response;
	}
}
