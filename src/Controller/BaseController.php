<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    protected function crearForm($type, Usuario $usuario, array $options = array(), Request $request)
    {
        if ('json' === $request->getContentType()) {
            $options['csrf_protection'] = false;
        }

        return $this->createForm($type, $usuario, $options);
    }

    protected function procesarForm(FormInterface $form, Request $request)
    {
        if ('json' === $request->getContentType()) {
            $data = array();
            if ('DELETE' !== $request->getMethod()) {
                $data = json_decode($request->getContent(), true);
            }
            $form->submit($data);
        }
        else {
            $form->handleRequest($request);
        }

        return $form;
    }
}
