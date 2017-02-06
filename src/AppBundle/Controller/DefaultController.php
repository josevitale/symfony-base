<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;

class DefaultController extends FOSRestController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        $templateData = array('base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,);
        $view = $this->view(array(1, 2, 3), 200)
                ->setTemplate('default/index.html.twig')
                ->setTemplateData($templateData)
        ;

        return $this->handleView($view);
    }
}
