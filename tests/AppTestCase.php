<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\Usuario;

class AppTestCase extends WebTestCase
{
    private static $staticClient;

    /**
     *
     * @var Client
     */
    protected $client;

    public static function setUpBeforeClass()
    {
        self::$staticClient = static::createClient();
    }

    protected function setUp()
    {
        $this->client = self::$staticClient;
    }

    protected function tearDown()
    {
    }

    protected function crearUsuario($username, $plainPassword = 'foo')
    {
        $usuario = new Usuario();
        $usuario->setUsername($username);
        $usuario->setEmail($username.'@foo.com');
        $usuario->setRoles(array('ROLE_ADMIN'));
        $password = $this->client->getContainer()->get('security.password_encoder')
            ->encodePassword($usuario, $plainPassword);
        $usuario->setPassword($password);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $em->persist($usuario);
        $em->flush();

        return $usuario;
    }

    protected function eliminarUsuario($username)
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $usuario = $em->getRepository('App:Usuario')->findOneByUsername($username);
        $em->remove($usuario);
        $em->flush();
    }

    public function logIn(array $roles = array('ROLE_ADMIN'))
    {
        $session = $this->client->getContainer()->get('session');
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $usuario = $em->getRepository('App:Usuario')->findOneByUsername('test');

        $token = new UsernamePasswordToken($usuario, null, 'main', $roles);
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
