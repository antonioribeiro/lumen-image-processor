<?php

namespace App\Services;

use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;

class FileFinder {

	private $filesystem;

	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	public function find($file)
	{
		$dirname = dirname($file);

		if ( ! $this->filesystem->exists($dirname))
		{
			$this->filesystem->makeDirectory($dirname, 0775, true); // true = recursive

			return false;
		}

		if ( ! $this->filesystem->exists($file))
		{
			return false;
		}

		return true;
	}

}
