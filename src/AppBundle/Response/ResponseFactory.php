<?php

namespace AppBundle\Response;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use AppBundle\Exception\ErrorException;

class ResponseFactory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function crearResponse(Request $request, ResponseData $responseData)
    {
        if (in_array('application/json' , $request->getAcceptableContentTypes())) {
            $response = $this->crearJsonResponse($request, $responseData);
        }
        elseif (in_array('text/html' , $request->getAcceptableContentTypes())) {
            $response = $this->crearHtmlResponse($request, $responseData);
        }
        else {
            $response = $this->crearHtmlResponse($request, $responseData);
        }

        return $response;
    }

    protected function crearHtmlResponse(Request $request, ResponseData $responseData)
    {
        if ($responseData->isRedirection() || $responseData->hasToRedirect()){
            $response = $this->getRedirectResponse($responseData);
        }
        elseif ($responseData->isSuccess() || $responseData->isError()) {
            $response = $this->getRenderResponse($request, $responseData);
        }
        else {
            $responseError = new ResponseError(500, ResponseError::ERROR_APLICACION);

            throw new ErrorException($responseError);
        }
        $this->addFlashes($responseData);

        return $response;
    }

    protected function crearJsonResponse(Request $request, ResponseData $responseData)
    {
        $serializer = $this->container->get('serializer');
        $response = new Response(
            $serializer->serialize($responseData->toArray(), 'json'),
            $responseData->getStatusCode()
        );
        if ($responseData instanceof ResponseError) {
            $response->headers->set('Content-Type', 'application/problem+json');
        }
        else {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    protected function addFlashes(ResponseData $responseData)
    {
        if (!$this->container->has('session')) {
            return;
        }
        $mensajes = $responseData->getMensajes();
        foreach (array_keys($mensajes) as $tipo) {
            foreach ($mensajes[$tipo] as $mensaje) {
                $this->container->get('session')->getFlashBag()->add($tipo, $mensaje);
            }
        }
    }

    protected function getControllerAction(Request $request)
    {
        $controllerRequest = explode("Controller::", $request->attributes->get('_controller'));
        $controllerRequest[0] = explode("\\", $controllerRequest[0]);
        $controllerRequest[0] = $controllerRequest[0][count($controllerRequest[0]) - 1];
        $controller = array(
            'controller' => strtolower($controllerRequest[0]),
            'action' => str_replace('Action', '', $controllerRequest[1]),
        );

        return $controller;
    }

    protected function getControllerConfig($controller)
    {
        $controllerConfigFile = $this->container->get('kernel')->getRootDir() . '/Resources/config/controller/' . $controller['controller'] . '.yml';
        if (file_exists($controllerConfigFile)) {
            $controllerConfig = Yaml::parseFile($controllerConfigFile);
        }
        else {
            $controllerConfig = null;
        }

        return $controllerConfig;
    }

    protected function getRenderResponse(Request $request, ResponseData $responseData)
    {
        $view = $this->getView($request, $responseData);
        if(null === $view) {
            $responseError = new ResponseError(500, ResponseError::ERROR_APLICACION);

            throw new ErrorException($responseError);
        }
        $renderView = $this->renderView($view, $responseData);
        $response = new Response($renderView, $responseData->getStatusCode(), $responseData->getHeaders());
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    protected function getView(Request $request, ResponseData $responseData)
    {
        $controller = $this->getControllerAction($request);
        $controllerConfig = $this->getControllerConfig($controller);
        if ($responseData->isError() && !$responseData->isPostError()) {

            return 'error.html.twig';
        }
        elseif ($controllerConfig && array_key_exists('template', $controllerConfig[$controller['action']])) {

            return $controllerConfig[$controller['action']]['template'];
        }
    }

    protected function renderView($view, ResponseData $responseData)
    {
        $datos = array();
        foreach ($responseData->toArray() as $key => $data) {
            if ($data instanceof FormInterface) {
                $datos[$key] = $data->createView();
            }
            else {
                $datos[$key] = $data;
            }
        }

        return $this->container->get('templating')->render($view, $datos);
    }

    protected function getRedirectResponse(ResponseData $responseData)
    {
        if ($responseData->isRedirection())
        {
            $response = new RedirectResponse($responseData->getHeader('Location'), $responseData->getStatusCode(), $responseData->getHeaders());
        }
        elseif ($responseData->hasToRedirect()) {
            $response = new RedirectResponse($responseData->getRedirect()->getHeader('Location'), $responseData->getRedirect()->getStatusCode(), $responseData->getRedirect()->getHeaders());
        }
        else {
            $responseError = new ResponseError(500, ResponseError::ERROR_APLICACION);

            throw new ErrorException($responseError);
        }

        return $response;
    }
}
