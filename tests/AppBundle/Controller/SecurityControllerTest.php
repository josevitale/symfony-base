<?php

namespace AppBundle\Tests\Controller;

use Tests\AppTestCase;

class SecurityControllerTest extends AppTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->createUser('test');
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteUser('test');
    }

    public function testWebLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /login] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#security_login_titulo')->count(), "[GET /login] Elemento html no encotrado: 'security_login_titulo'");
        $this->assertEquals(1, $crawler->filter('#security_login_login')->count(), "[GET /login] Elemento html no encotrado: 'security_login_login'");
        $form = $crawler->filter('#security_login_login')->form();
        $form['appbundle_login[_username]'] = 'test';
        $form['appbundle_login[_password]'] = 'foo';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[POST /login] StatusCode inesperado");

        $crawlerLogin = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /] StatusCode inesperado");
        $this->assertEquals(1, $crawlerLogin->filter('#homepage_titulo')->count(), "[GET /] Elemento html no encotrado: 'homepage_titulo'");
        $this->assertEquals(1, $crawlerLogin->filter('#base_logged_in_as')->count(), "[GET /] Elemento html no encotrado: 'base_logged_in_as'");
        $this->assertEquals(1, $crawlerLogin->filter('#base_user_edit')->count(), "[GET /] Elemento html no encotrado: 'base_user_edit'");
        $this->assertEquals(1, $crawlerLogin->filter('#base_security_logout')->count(), "[GET /] Elemento html no encotrado: 'base_security_logout'");

        $linkLogout = $crawlerLogin->filter('#base_security_logout')->link();
        $crawlerLogout = $this->client->click($linkLogout);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[GET /logout] StatusCode inesperado");
        $crawlerHome = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /] StatusCode inesperado");
        $this->assertEquals(1, $crawlerHome->filter('#homepage_titulo')->count(), "[GET /] Elemento html no encotrado: 'homepage_titulo'");
        $this->assertEquals(1, $crawlerHome->filter('#base_user_login')->count(), "[GET /] Elemento html no encotrado: 'base_user_login'");
    }

    public function testWebErrorLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /login] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#security_login_titulo')->count(), "[GET /login] Elemento html no encotrado: 'security_login_titulo'");
        $this->assertEquals(1, $crawler->filter('#security_login_login')->count(), "[GET /login] Elemento html no encotrado: 'security_login_login'");
        $form = $crawler->filter('#security_login_login')->form();
        $form['appbundle_login[_username]'] = 'test';
        $form['appbundle_login[_password]'] = 'fool';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[POST /login] StatusCode inesperado");

        $crawlerLogin = $this->client->followRedirect();
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode(), "[GET /login] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#security_login_titulo')->count(), "[GET /login] Elemento html no encotrado: 'security_login_titulo'");
        $this->assertEquals(1, $crawler->filter('#security_login_login')->count(), "[GET /login] Elemento html no encotrado: 'security_login_login'");
    }

    public function testJsonLogin()
    {
        $data = json_encode(array(
            '_username' => 'test',
            '_password' => 'foo',
        ));
        $crawler = $this->client->request('POST', '/login', array(), array(), array(
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ), $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('token', json_decode($this->client->getResponse()->getContent(), true));
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $crawler = $this->client->request('GET', '/groups/', array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('groups', json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testJsonErrorLogin()
    {
        $data = json_encode(array(
            '_username' => 'test',
            '_password' => 'fool',
        ));
        $crawler = $this->client->request('POST', '/login', array(), array(), array(
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ), $data);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}
