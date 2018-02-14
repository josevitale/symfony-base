<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use AppBundle\Response\ResponseFactory;
use AppBundle\Response\ResponseData;

class ViewResponseSubscriber implements EventSubscriberInterface
{
    protected $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView',
        );
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        if ($event->getControllerResult() instanceof  ResponseData) {
            $controllerResult = $event->getControllerResult();
        }
        elseif (is_array($event->getControllerResult())) {
            $controllerResult = new ResponseData($event->getControllerResult(), 200);
        }
        else  {
            return;
        }
        $response = $this->responseFactory->crearResponse($event->getRequest(), $controllerResult);
        $event->setResponse($response);
    }
}
