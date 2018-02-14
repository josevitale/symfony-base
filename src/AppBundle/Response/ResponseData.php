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

    /**
     *
     * @var Form
     */
    protected $form;

    public function __construct($datos = array(), $statusCode = 200, $form = null)
    {
        $this->statusCode = $statusCode;
        $this->datos = $datos;
        $this->form = $form;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getDatos()
    {
        return $this->datos;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function set($nombre, $valor)
    {
        $this->datos[$nombre] = $valor;
    }

    public function toArray()
    {
        return $this->getDatos();
    }
}
