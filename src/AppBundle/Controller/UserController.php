<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserType;
use AppBundle\Response\ResponseError;
use AppBundle\Exception\ErrorException;

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

        return $this->render('user/list.html.twig', array(
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

        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
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
            $this->addFlash('success', $translator->trans('user.new.usuario_creado'));

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function showAction(User $user)
    {
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        return $this->render('user/show.html.twig', array(
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

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
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
            $this->addFlash('success', $translator->trans('user.edit.usuario_modificado'));

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function removeAction(Request $request, User $user)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        return $this->render('user/remove.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
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
            $this->addFlash('success', $translator->trans('user.remove.usuario_eliminado'));
        }

        return $this->redirectToRoute('user_list');
    }
}
