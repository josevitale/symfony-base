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
}
