<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Form\LoginType;
use App\Response\ResponseData;
use App\Response\ResponseError;

class SecurityController extends Controller
{
    public function login(Request $request)
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

    public function logout()
    {
    }
}
