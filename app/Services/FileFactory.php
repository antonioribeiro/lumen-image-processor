<?php

namespace App\Services;

class FileFactory {

	public function make($request)
	{
		return app()->make('App\Services\File', [$request]);
	}

}
