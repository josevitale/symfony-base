<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Response\ResponseError;
use App\Response\ResponseFactory;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtPassword;
    private $em;
    private $responseFactory;

    public function __construct(string $jwtPassword, EntityManagerInterface $em, ResponseFactory $responseFactory)
    {
        $this->jwtPassword = $jwtPassword;
        $this->em = $em;
        $this->responseFactory = $responseFactory;
    }

    public function getCredentials(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $payload = JWT::decode($token, $this->jwtPassword);
        $data['_username'] = $payload['_username'];

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
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $responseError = new ResponseError(401, ResponseError::ERROR_CREDENCIALES);

        return $this->responseFactory->crearResponse($request, $responseError);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $responseError = new ResponseError(401, ResponseError::ERROR_CREDENCIALES);

        return $this->responseFactory->crearResponse($request, $responseError);
    }
}
