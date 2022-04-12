<?php

namespace FriendsOfBabba\Core\Error;

use Crud\Error\Exception\ValidationException;
use Cake\Http\Response;
use Crud\Error\ExceptionRenderer;
use FriendsOfBabba\Core\Routing\Middleware\CorsMiddleware;
use Psr\Http\Message\ResponseInterface;

class AppExceptionRenderer extends ExceptionRenderer
{
	public function validation(ValidationException $error): Response
	{
		$response = parent::validation($error);

		$cors = new CorsMiddleware();

		$response = $cors->addHeaders($this->request, $response);

		return $response;
	}

	public function render(): ResponseInterface
	{
		$response = parent::render();

		$cors = new CorsMiddleware();

		$response = $cors->addHeaders($this->request, $response);

		return $response;
	}
}
