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
}
