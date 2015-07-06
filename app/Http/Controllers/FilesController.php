<?php

namespace App\Http\Controllers;

use App\Services\Processor;
use Illuminate\Http\Request;

class FilesController extends Controller
{

	private $request;

	private $processor;

	public function __construct(Request $request, Processor $processor)
	{
		$this->request = $request;

		$this->processor = $processor;

		$this->processor->setResponse(response());
	}

	public function process()
	{
		return $this->processor->process($this->request);
    }

}
