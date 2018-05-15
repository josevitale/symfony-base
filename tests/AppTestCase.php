<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

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

    protected function createUser($username, $plainPassword = 'foo')
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@foo.com');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_ADMIN'));
        $password = $this->client->getContainer()->get('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function deleteUser($username)
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByUsername($username);
        $em->remove($user);
        $em->flush();
    }

    public function logIn(array $roles = array('ROLE_ADMIN'))
    {
        $session = $this->client->getContainer()->get('session');
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByUsername('test');

        $token = new UsernamePasswordToken($user, null, 'main', $roles);
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
