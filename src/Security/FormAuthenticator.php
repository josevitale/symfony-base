<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Form\LoginType;
use App\Response\ResponseData;
use App\Response\ResponseError;
use App\Response\ResponseFactory;

class FormAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtPassword;
    private $formFactory;
    private $em;
    private $router;
    private $passwordEncoder;
    private $responseFactory;

    public function __construct($jwtPassword, FormFactoryInterface $formFactory, EntityManagerInterface $em, RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder, ResponseFactory $responseFactory)
    {
        $this->jwtPassword = $jwtPassword;
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->responseFactory = $responseFactory;
    }

    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(LoginType::class);
        if ('json' === $request->getContentType()) {
            $data = json_decode($request->getContent(), true);
        }
        else {
            $form->handleRequest($request);
            $data = $form->getData();
        }
        if (null !== $data && array_key_exists('_username', $data)) {
            $request->getSession()->set(
                Security::LAST_USERNAME,
                $data['_username']
            );
        }

        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        return $this->em->getRepository('App:Usuario')
            ->findOneBy(['username' => $username]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];

        if ($this->passwordEncoder->isPasswordValid($user, $password)) {

            return true;
        }

        return false;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $responseError = new ResponseError(401, ResponseError::ERROR_CREDENCIALES);
        $responseError->redirect($this->getLoginUrl());
        if ($request->getSession() instanceof SessionInterface) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return $this->responseFactory->crearResponse($request, $responseError);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $response = new ResponseData();
        $response->redirect($this->router->generate('homepage'));
        if (in_array('application/json' , $request->getAcceptableContentTypes())) {
            $payload = array(
                '_username' => $token->getUser()->getUsername(),
                'exp' => time() + 3600,
            );
            $response->set('token', JWT::encode($payload, $this->jwtPassword));
        }

        return $this->responseFactory->crearResponse($request, $response);
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $responseError = new ResponseError(401, ResponseError::ERROR_CREDENCIALES);
        $responseError->redirect($this->getLoginUrl());

        return $this->responseFactory->crearResponse($request, $responseError);
    }
}
