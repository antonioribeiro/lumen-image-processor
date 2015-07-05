<?php

namespace App\Services;

use Intervention\Image\ImageManager;

class Image {

	private $manager;

	private $image;

	private $values;

	private $fileName;

	public function __construct()
	{
		$this->manager = new ImageManager(['driver' => 'imagick']);
	}

	public function setFilename($fileName)
	{
		$this->fileName = $fileName;

		$this->makeManager();

		return $this;
	}

	public function transform($command, $value = null)
	{
		$this->makeManager();

		$this->values = explode(' ', $value);

		$zero = $this->getValue(0);
		$one = $this->getValue(1);
		$two = $this->getValue(2);
		$three = $this->getValue(3);
		$four = $this->getValue(4);

		if ($command == 'width')
		{
			$this->image->resize($zero, null, $this->checkConstraint($one ? $one : 'aspect'));
		}
		elseif ($command == 'blur')
		{
			$this->image->blur($this->getValue(0, 15));
		}
		elseif ($command == 'brightness')
		{
			$this->image->brightness($this->getValue(0, 25));
		}
		elseif ($command == 'brightness')
		{
			$this->image->brightness($this->getValue(0, 25));
		}
		elseif ($command == 'canvas')
		{

		}
		elseif ($command == 'circle')
		{

		}
		elseif ($command == 'colorize')
		{
			$this->image->colorize($zero, $one, $two);
		}
		elseif ($command == 'contrast')
		{
			$this->image->contrast($zero);
		}
		elseif ($command == 'crop')
		{
			$this->image->crop($zero, $one, $two, $three);
		}
		elseif ($command == 'destroy')
		{

		}
		elseif ($command == 'ellipse')
		{

		}
		elseif ($command == 'encode')
		{

		}
		elseif ($command == 'exif')
		{

		}
		elseif ($command == 'filesize')
		{

		}
		elseif ($command == 'fill')
		{
			$this->image->fill($zero, $one, $two);
		}
		elseif ($command == 'flip')
		{
			$this->image->flip($zero);
		}
		elseif ($command == 'fit')
		{
			$this->image->fit($zero, $one, $this->checkConstraint($two), $two);
		}
		elseif ($command == 'gamma')
		{
			$this->image->gamma($zero);
		}
		elseif ($command == 'getCore')
		{

		}
		elseif ($command == 'greyscale')
		{
			$this->image->greyscale();
		}
		elseif ($command == 'height')
		{

		}
		elseif ($command == 'heighten')
		{
			$this->image->heighten($zero, $this->checkConstraint($two));
		}
		elseif ($command == 'insert')
		{
			$this->image->insert($zero, $one, $two, $three);
		}
		elseif ($command == 'interlace')
		{
			$this->image->interlace($zero == 'yes');
		}
		elseif ($command == 'invert')
		{
			$this->image->invert();
		}
		elseif ($command == 'iptc')
		{

		}
		elseif ($command == 'limitColors')
		{
			$this->image->limitColors($zero, $one);
		}
		elseif ($command == 'line')
		{
			$this->image->line($zero, $one, $two, $three);
		}
		elseif ($command == 'make')
		{

		}
		elseif ($command == 'mask')
		{
			$this->image->mask($zero, $one == 'alpha');
		}
		elseif ($command == 'mime')
		{

		}
		elseif ($command == 'opacity')
		{
			$this->image->opacity($zero);
		}
		elseif ($command == 'orientate')
		{
			$this->image->orientate();
		}
		elseif ($command == 'pickColor')
		{

		}
		elseif ($command == 'pixel')
		{
			$this->image->pixel($zero, $one, $two);
		}
		elseif ($command == 'pixelate')
		{
			$this->image->pixelate($zero);
		}
		elseif ($command == 'polygon')
		{

		}
		elseif ($command == 'reset')
		{

		}
		elseif ($command == 'resize')
		{
			$this->image->resize($zero, $one, $this->checkConstraint($two, $three));
		}
		elseif ($command == 'resizeCanvas')
		{
			$this->image->resizeCanvas($zero, $one, $two, $three, $four);
		}
		elseif ($command == 'response')
		{
			$this->image->response($zero, $one);
		}
		elseif ($command == 'rotate')
		{
			$this->image->rotate($zero, $one);
		}
		elseif ($command == 'save')
		{

		}
		elseif ($command == 'sharpen')
		{
			$this->image->sharpen($zero);
		}
		elseif ($command == 'text')
		{
			$this->image->text($zero, $one, $two);
		}
		elseif ($command == 'trim')
		{

		}
		elseif ($command == 'widen')
		{
			$this->image->widen($zero, $this->checkConstraint($one));
		}
		elseif ($command == 'width')
		{

		}

		return $this;
	}

	public function setImage($image)
	{
		$this->image = $image;

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

	private function getValue($pos, $default = null)
	{
		if (isset($this->values[$pos]))
		{
			return $this->values[$pos];
		}

		return $default;
	}

	private function checkConstraint($zero, $one = null)
	{
		$constraint = function ($constraint) use ($zero, $one)
		{
			if ($zero == 'upzise' || $one == 'upzise')
			{
				$constraint->upsize();
			}

			if ($zero == 'aspect' || $one == 'aspect')
			{
				$constraint->aspectRatio();
			}
		};

		return $constraint;
	}

	private function makeManager()
	{
		if (file_exists($this->fileName) && ! $this->image)
		{
			$this->image = $this->manager->make($this->fileName);
		}
	}

}
