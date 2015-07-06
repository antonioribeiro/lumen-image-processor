<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Factory as Filesystem;

class FileFinder
{
	private $filesystem;

	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	public function find($file, $recursivelyCreateDirectories = true)
	{
		$dirname = dirname($file);

		if ( ! $this->filesystem->exists($dirname))
		{
			if ($recursivelyCreateDirectories)
			{
				$this->filesystem->makeDirectory($dirname, 0775, true);
			}

			return false;
		}

		if ( ! $this->filesystem->exists($file))
		{
			return false;
		}

		return true;
	}
}
