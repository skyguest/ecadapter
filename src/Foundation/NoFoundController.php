<?php
namespace Skyguest\Ecadapter\Foundation;
use Symfony\Component\HttpFoundation\Response;

class NoFoundController {
	
	public function index() {
		return Response::create('Not Found!', 404);
	}
}