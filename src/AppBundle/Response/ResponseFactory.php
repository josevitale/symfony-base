<?php

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

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
        $controllerRequest = explode("Controller::", $request->attributes->get('_controller'));
        $controllerRequest[0] = explode("\\", $controllerRequest[0]);
        $controllerRequest[0] = $controllerRequest[0][count($controllerRequest[0]) - 1];
        $controller = array(
            'controller' => strtolower($controllerRequest[0]),
            'action' => str_replace('Action', '', $controllerRequest[1]),
        );
        $controllerConfig = Yaml::parseFile($this->container->get('kernel')->getRootDir() . '/Resources/config/controller/' . $controller['controller'] . '.yml');
        $view = $controllerConfig[$controller['action']]['template'];
        if ($responseData instanceof ResponseError) {
            $view = 'error.html.twig';
            if (array_key_exists('template_error', $controllerConfig[$controller['action']])) {
                $view = $controllerConfig[$controller['action']]['template_error'];
            }
        }
        $renderView = $this->container->get('templating')->render($view, $responseData->toArray());
        $response = new Response($renderView, $responseData->getStatusCode());
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    protected function crearJsonResponse(Request $request, ResponseData $responseData)
    {
        $response = new JsonResponse(
            $responseData->toArray(),
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
}
