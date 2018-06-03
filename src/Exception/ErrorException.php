<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Response\ResponseError;

class ErrorException extends HttpException
{
    protected $responseError;

    public function __construct(ResponseError $responseError, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->responseError = $responseError;
        parent::__construct($responseError->getStatusCode(), $responseError->getTitulo(), $previous, $headers, $code);
    }

    public function getResponseError()
    {
        return $this->responseError;
    }
}
