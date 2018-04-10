<?php

namespace AppBundle\Response;

use Symfony\Component\Form\Form;

class ResponseData
{
    /**
     *
     * @var int
     */
    protected $statusCode;

    /**
     *
     * @var array
     */
    protected $datos;

    public function __construct($datos = array(), $statusCode = 200)
    {
        $this->statusCode = $statusCode;
        $this->datos = $datos;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getDatos()
    {
        return $this->datos;
    }

    public function set($nombre, $valor)
    {
        $this->datos[$nombre] = $valor;
    }

    public function toArray()
    {
        return array_merge(
            array(
                'statusCode' => $this->getStatusCode(),
            ),
            $this->getDatos()
        );
    }

    public function isSuccess()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isError()
    {
        return $this->isClientError() || $this->isServerError();
    }
}
