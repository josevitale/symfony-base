<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Usuario;
use AppBundle\Exception\ErrorException;
use AppBundle\Form\UsuarioType;
use AppBundle\Response\ResponseData;
use AppBundle\Response\ResponseError;
use AppBundle\Response\ResponseMensaje;

class UsuarioController extends Controller
{

    public function listAction()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $em = $this->getDoctrine()->getManager();
        $usuarios = $em->getRepository('AppBundle:Usuario')->findAll();

        return new ResponseData(array(
            'usuarios' => $usuarios,
        ));
    }

    public function newAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        $response = new ResponseData();
        $response->setForm($form);

        return $response;
    }

    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $this->get('security.password_encoder')
                ->encodePassword($usuario, $usuario->getPlainPassword());
            $usuario->setPassword($password);
            $em->persist($usuario);
            $em->flush();
            $translator = $this->get('translator');
            $response = new ResponseData(array(), 201);
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('usuario.new.usuario_creado'));
            $response->setHeader('Location', $this->generateUrl('usuario_show', array('id' => $usuario->getId())));
            $response->redirect($this->generateUrl('usuario_show', array('id' => $usuario->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->setForm($form);

        return $response;
    }

    public function showAction(Usuario $usuario)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $usuario->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        return new ResponseData(array(
            'usuario' => $usuario,
        ));
    }

    public function editAction(Request $request, Usuario $usuario)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $usuario->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(UsuarioType::class, $usuario, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        $response = new ResponseData(array(
            'usuario' => $usuario,
        ));
        $response->setForm($form);

        return $response;
    }

    public function updateAction(Request $request, Usuario $usuario)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $usuario->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(UsuarioType::class, $usuario, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $translator = $this->get('translator');
            $response = new ResponseData();
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('usuario.edit.usuario_modificado'));
            $response->redirect($this->generateUrl('usuario_show', array('id' => $usuario->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->set('usuario', $usuario);
        $response->setForm($form);

        return $response;
    }

    public function removeAction(Request $request, Usuario $usuario)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);
        $response = new ResponseData(array(
            'usuario' => $usuario,
        ));
        $response->setForm($form);

        return $response;
    }

    public function deleteAction(Request $request, Usuario $usuario)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($usuario);
            $em->flush();
            $translator = $this->get('translator');
            $response = new ResponseData(array(), 204);
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('usuario.remove.usuario_eliminado'));
        }
        else {
            $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        }
        $response->redirect($this->generateUrl('usuario_list'));

        return $response;
    }
}
