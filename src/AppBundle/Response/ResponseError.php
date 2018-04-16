<?php

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class ResponseError extends ResponseData
{
    const ERROR_VALIDACION = 'error_validacion';
    const ERROR_CREDENCIALES = 'error_credenciales';
    const ERROR_ACCESO_DENEGADO = 'error_acceso_denegado';
    const ERROR_APLICACION = 'error_aplicacion';
    const ERROR_DESCONOCIDO = 'error_desconocido';

    protected static $titulos = array(
        self::ERROR_VALIDACION => 'Hubo un error de validación',
        self::ERROR_CREDENCIALES => 'Usuario o contraseña incorrectos',
        self::ERROR_ACCESO_DENEGADO => 'No tiene permisos para realizar esta operación',
        self::ERROR_APLICACION => 'Error interno en la aplicación',
        self::ERROR_DESCONOCIDO => 'Error desconocido',
    );

    protected $tipo;

    protected $titulo;

    public function __construct($statusCode, $tipo = self::ERROR_DESCONOCIDO) {
        if (self::ERROR_DESCONOCIDO === $tipo) {
            $titulo = isset(Response::$statusTexts[$statusCode]) ? Response::$statusTexts[$statusCode] : self::$titulos[$tipo];
        }
        else {
            if (!isset(self::$titulos[$tipo])) {
                throw new \InvalidArgumentException('No existe título para el tipo "' . $tipo . '"');
            }
            $titulo = self::$titulos[$tipo];
        }
        parent::__construct(array(), $statusCode);
        $this->tipo = $tipo;
        $this->titulo = $titulo;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function isPostError()
    {
        return self::ERROR_CREDENCIALES === $this->getTipo() || self::ERROR_VALIDACION === $this->getTipo();
    }

    public function toArray()
    {
        return array_merge(
            array(
                'tipo' => $this->getTipo(),
                'titulo' => $this->getTitulo(),
            ),
            parent::toArray()
        );
    }
}
