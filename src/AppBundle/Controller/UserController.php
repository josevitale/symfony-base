<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\UserType;

class UserController extends Controller
{

    public function listAction()
    {
        $translator = $this->get('translator');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $translator->trans('general.acceso_denegado'));

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('user/list.html.twig', array(
            'users' => $users,
        ));
    }

    public function newAction(Request $request)
    {
        $translator = $this->get('translator');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $translator->trans('general.acceso_denegado'));

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
        $translator = $this->get('translator');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $translator->trans('general.acceso_denegado'));

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
        $translator = $this->get('translator');
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException($translator->trans('general.acceso_denegado'));
        }

        return $this->render('user/show.html.twig', array(
            'user' => $user,
        ));
    }

    public function editAction(Request $request, User $user)
    {
        $translator = $this->get('translator');
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException($translator->trans('general.acceso_denegado'));
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
        $translator = $this->get('translator');
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException($translator->trans('general.acceso_denegado'));
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
        $translator = $this->get('translator');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $translator->trans('general.acceso_denegado'));

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        return $this->render('user/remove.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, User $user)
    {
        $translator = $this->get('translator');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $translator->trans('general.acceso_denegado'));

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
