<?php

namespace AppBundle\Response;

use Symfony\Component\Form\FormView;

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
     * @var array
     */
    protected $headers;

    /**
     *
     * @var FormView
     */
    protected $form;

    /**
     *
     * @var array
     */
    protected $mensajes;

    /**
     *
     * @var ResponseData
     */
    protected $redirect;

    public function __construct($datos = array(), $statusCode = 200, $headers = array())
    {
        $this->statusCode = $statusCode;
        $this->datos = $datos;
        $this->headers = $headers;
        $this->mensajes = array();
        $this->esControllerResponse = true;
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

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($nombre)
    {
        if (!array_key_exists($nombre, $this->headers)) {

            return null;
        }

        return $this->headers[$nombre];
    }

    public function setHeader($nombre, $valor)
    {
        $this->headers[$nombre] = $valor;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setForm(FormView $form)
    {
        $this->form = $form;

        return $this;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function hasToRedirect()
    {
        return null !== $this->redirect;
    }

    public function getMensajes()
    {
        return $this->mensajes;
    }

    public function addMensaje($tipo, $mensaje)
    {
        if (array_key_exists($tipo, $this->mensajes)) {
            $this->mensajes[$tipo][] = $mensaje;
        }
        else {
            $this->mensajes[$tipo] = array($mensaje);
        }

        return $this;
    }

    public function redirect($url, $status = 302)
    {
        $responseRedirect = new ResponseData(Array(), $status);
        $responseRedirect->setHeader('Location', $url);
        $this->redirect = $responseRedirect;

        return $this;
    }

    public function toArray()
    {
        return array_merge(
            array(
                'statusCode' => $this->getStatusCode(),
                'form' => $this->getForm(),
                'mensajes' => $this->getMensajes(),
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
