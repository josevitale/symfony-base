<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Exception\ErrorException;
use AppBundle\Form\UserType;
use AppBundle\Response\ResponseData;
use AppBundle\Response\ResponseError;
use AppBundle\Response\ResponseMensaje;

class UserController extends Controller
{

    public function listAction()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();

        return new ResponseData(array(
            'users' => $users,
        ));
    }

    public function newAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
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

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $translator = $this->get('translator');
            $response = new ResponseData(array(), 201);
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('user.new.usuario_creado'));
            $response->setHeader('Location', $this->generateUrl('user_show', array('id' => $user->getId())));
            $response->redirect($this->generateUrl('user_show', array('id' => $user->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->setForm($form);

        return $response;
    }

    public function showAction(User $user)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        return new ResponseData(array(
            'user' => $user,
        ));
    }

    public function editAction(Request $request, User $user)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(UserType::class, $user, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        $response = new ResponseData(array(
            'user' => $user,
        ));
        $response->setForm($form);

        return $response;
    }

    public function updateAction(Request $request, User $user)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(UserType::class, $user, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $translator = $this->get('translator');
            $response = new ResponseData();
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('user.edit.usuario_modificado'));
            $response->redirect($this->generateUrl('user_show', array('id' => $user->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->set('user', $user);
        $response->setForm($form);

        return $response;
    }

    public function removeAction(Request $request, User $user)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);
        $response = new ResponseData(array(
            'user' => $user,
        ));
        $response->setForm($form);

        return $response;
    }

    public function deleteAction(Request $request, User $user)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->deleteUser($user);
            $translator = $this->get('translator');
            $response = new ResponseData(array(), 204);
            $response->addMensaje(ResponseMensaje::SUCCESS, $translator->trans('user.remove.usuario_eliminado'));
        }
        else {
            $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        }
        $response->redirect($this->generateUrl('user_list'));

        return $response;
    }
}
