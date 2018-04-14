<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\GroupType;
use AppBundle\Response\ResponseData;
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

        return new ResponseData(array(
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
        $response = new ResponseData();
        $response->setForm($form->createView());

        return $response;
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
            $response = new ResponseData(array(), 201);
            $response->addMensaje('success', $translator->trans('group.new.grupo_creado'));
            $response->setHeader('Location', $this->generateUrl('group_show', array('id' => $group->getId())));
            $response->redirect($this->generateUrl('group_show', array('id' => $group->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->set('form', $form->createView());

        return $response;
    }

    public function showAction(Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        return new ResponseData(array(
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
        $response = new ResponseData(array(
            'group' => $group,
        ));
        $response->setForm($form->createView());

        return $response;
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
            $response = new ResponseData();
            $response->addMensaje('success', $translator->trans('group.edit.grupo_modificado'));
            $response->redirect($this->generateUrl('group_show', array('id' => $group->getId())));

            return $response;
        }
        $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        $response->set('group', $group);
        $response->set('form', $form->createView());

        return $response;
    }

    public function removeAction(Request $request, Group $group)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $error = new ResponseError(403, ResponseError::ERROR_ACCESO_DENEGADO);

            throw new ErrorException($error);
        }

        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();
        $form->handleRequest($request);
        $response = new ResponseData(array(
            'group' => $group,
        ));
        $response->setForm($form->createView());

        return $response;
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
            $response = new ResponseData(array(), 204);
            $response->addMensaje('success', $translator->trans('group.remove.grupo_eliminado'));
        }
        else {
            $response = new ResponseError(400, ResponseError::ERROR_VALIDACION);
        }
        $response->redirect($this->generateUrl('group_list'));

        return $response;
    }
}
