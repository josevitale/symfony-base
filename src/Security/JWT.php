<?php

namespace App\Security;

class JWT
{
    protected $header;
    protected $payload;
    protected $signature;

    public function __construct($payload, $header = array('alg' => 'HS256', 'typ' => 'JWT'), $signature = null)
    {
        $this->header = $header;
        $this->payload = $payload;
        $this->signature = $signature;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function generarCadenaParaFirmar()
    {
        $base64header = base64_encode(json_encode($this->getHeader(), true));
        $base64payload = base64_encode(json_encode($this->getPayload(), true));

        return sprintf('%s.%s', $base64header, $base64payload);
    }

    public function firmar($password)
    {
        $this->signature = hash_hmac('sha256', $this->generarCadenaParaFirmar(), $password);

        return $this->signature;
    }

    public function getToken()
    {
        $cadenaParaFirmar = $this->generarCadenaParaFirmar();

        return sprintf('%s.%s', $cadenaParaFirmar, base64_encode($this->getSignature()));
    }

    public function esValido($password)
    {
        $signatureCalc = hash_hmac('sha256', $this->generarCadenaParaFirmar(), $password);

        return hash_equals($this->signature, $signatureCalc);
    }

    public function estaVencido()
    {
        if (!array_key_exists('exp', $this->payload)) {

            return false;
        }

        return $this->payload['exp'] < time();
    }

    public static function cargar($token)
    {
        $segmentos = explode(".", $token);
        if (count($segmentos) === 3) {
            $header = json_decode(base64_decode($segmentos[0]), true);
            $payload = json_decode(base64_decode($segmentos[1]), true);
            $signature = base64_decode($segmentos[2]);
            if (is_array($header) && is_array($payload)) {
                $jwt = new static($payload, $header, $signature);

                return $jwt;
            }
        }

        return null;
    }

    public static function encode($payload, $password)
    {
        $jwt = new static($payload);
        if (null === $jwt) {

            return false;
        }
        $jwt->firmar($password);

        return $jwt->getToken();
    }

    public static function decode($token, $password)
    {
        $jwt = self::cargar($token);
        if (null === $jwt || !$jwt->esValido($password) || $jwt->estaVencido()) {

            return false;
        }

        return $jwt->getPayload();
    }
}
