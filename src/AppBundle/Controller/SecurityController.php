<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\LoginType;
use AppBundle\Response\ResponseData;
use AppBundle\Response\ResponseError;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $response = new ResponseError(401, ResponseError::ERROR_CREDENCIALES);
        }
        else {
            $response = new ResponseData();
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(LoginType::class, [
            '_username' => $lastUsername,
        ]);
        $response->setForm($form);

        return $response;
    }

    public function logoutAction()
    {
    }
}
