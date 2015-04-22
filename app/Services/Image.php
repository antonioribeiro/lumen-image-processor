<?php

namespace App\Services;

use Intervention\Image\ImageManager;

class Image {

	private $manager;

	private $image;

	public function __construct()
	{
		$this->manager = new ImageManager(['driver' => 'imagick']);
	}

	public function setFilename($fileName)
	{
		$this->image = $this->manager->make($fileName);

		return $this;
	}

	public function transform($command, $value)
	{
		if ($command == 'width')
		{
			$this->image->resize($value, null, function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		return $this;
	}

	public function getImage()
	{
		return $this->image;
	}

	function __call($name, $arguments)
	{
		$this->image = call_user_func_array([$this->image, $name], $arguments);

		return $this;
	}

}
