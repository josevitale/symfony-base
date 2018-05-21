<?php

namespace AppBundle\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use AppBundle\Response\ResponseError;
use AppBundle\Response\ResponseFactory;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $passwordEncoder;
    private $responseFactory;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

    public function __construct(EntityManager $em, UserPasswordEncoder $passwordEncoder, ResponseFactory $responseFactory, ParameterBag $parameterBag)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->responseFactory = $responseFactory;
        $this->parameterBag = $parameterBag;
    }

    public function getCredentials(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $password = $this->parameterBag->get('jwt_password');
        $payload = JWT::decode($token, $password);
        $data['_username'] = $payload['_username'];

        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        return $this->em->getRepository('AppBundle:User')
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
