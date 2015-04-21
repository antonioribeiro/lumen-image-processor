<?php

namespace App\Http\Controllers;

use App\Services\Processor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FilesController extends Controller
{

	private $request;

	private $processor;

	public function __construct(Request $request, Processor $processor)
	{
		$this->request = $request;

		$this->processor = $processor;
	}

	public function process()
	{
		$file = $this->processor->process($this->request);

		if ( ! $file->isValid())
		{
			return response([
				'success' => false,
				'error' => $file->getError(),
            ]);
		}

		return $file->download();
    }

}
