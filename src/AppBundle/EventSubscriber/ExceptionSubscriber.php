<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use AppBundle\Exception\ErrorException;
use AppBundle\Response\ResponseFactory;

class ExceptionSubscriber implements EventSubscriberInterface
{
    protected $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof ErrorException) {
            return;
        }
        $response = $this->responseFactory->crearResponse($event->getRequest(), $exception->getResponseError());
        $event->setResponse($response);
    }
}
