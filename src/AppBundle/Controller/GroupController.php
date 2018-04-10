<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\GroupType;
use AppBundle\Response\ResponseError;
use AppBundle\Exception\ErrorException;

class GroupController extends Controller
{
    public function listAction()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository('AppBundle:Group')->findAll();

        return $this->render('group/list.html.twig', array(
            'groups' => $groups,
        ));
    }

    public function newAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $groupManager = $this->get('fos_user.group_manager');
        $group = $groupManager->createGroup('');
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        return $this->render('group/new.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $groupManager = $this->get('fos_user.group_manager');
        $group = $groupManager->createGroup('');
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupManager = $this->get('fos_user.group_manager');
            $groupManager->updateGroup($group);
            $translator = $this->get('translator');
            $this->addFlash('success', $translator->trans('group.new.grupo_creado'));

            return $this->redirectToRoute('group_show', array('id' => $group->getId()));
        }

        return $this->render('group/new.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    public function showAction(Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        return $this->render('group/show.html.twig', array(
            'group' => $group,
        ));
    }

    public function editAction(Request $request, Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(GroupType::class, $group, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);

        return $this->render('group/edit.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    public function updateAction(Request $request, Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createForm(GroupType::class, $group, array(
            'method' => 'PUT',
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupManager = $this->get('fos_user.group_manager');
            $groupManager->updateGroup($group);
            $translator = $this->get('translator');
            $this->addFlash('success', $translator->trans('group.edit.grupo_modificado'));

            return $this->redirectToRoute('group_show', array('id' => $group->getId()));
        }

        return $this->render('group/edit.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    public function removeAction(Request $request, Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        return $this->render('group/remove.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupManager = $this->get('fos_user.group_manager');
            $groupManager->deleteGroup($group);
            $translator = $this->get('translator');
            $this->addFlash('success', $translator->trans('group.remove.grupo_eliminado'));
        }

        return $this->redirectToRoute('group_list');
    }
}
