<?php

namespace AppBundle\Response;

class ResponseError extends ResponseData
{
    const ERROR_VALIDACION = 'error_validacion';

    protected static $titulos = array(
        self::ERROR_VALIDACION => 'Hubo un error de validación',
    );

    protected $tipo;

    protected $titulo;

    public function __construct($statusCode, $tipo) {
        if (!isset(self::$titulos[$tipo])) {
            throw new \InvalidArgumentException('No existe título para el tipo "' . $tipo . '"');
        }
        parent::__construct(array(), $statusCode);
        $this->tipo = $tipo;
        $this->titulo = self::$titulos[$tipo];
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getTitulo()
    {
        return $this->titulo;
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
