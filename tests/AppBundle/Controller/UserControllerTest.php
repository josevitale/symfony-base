<?php

namespace AppBundle\Tests\Controller;

use Tests\AppTestCase;

class UserControllerTest extends AppTestCase
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

    public function testWebNew()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/new] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#user_new_titulo')->count(), "[GET /users/new] Elemento html no encotrado: 'user_new_titulo'");
        $this->assertEquals(1, $crawler->filter('#user_new_aceptar')->count(), "[GET /users/new] Elemento html no encotrado: 'user_new_aceptar'");
        $this->assertEquals(1, $crawler->filter('#user_new_cancelar')->count(), "[GET /users/new] Elemento html no encotrado: 'user_new_cancelar'");
        $form = $crawler->filter('#user_new_aceptar')->form();
        $linkCancelar = $crawler->filter('#user_new_cancelar')->link();
        $form['appbundle_user[email]'] = 'webtestusernew@test';
        $form['appbundle_user[username]'] = 'WebTestUserNew';
        $form['appbundle_user[plainPassword][first]'] = 'WebTestUserNew';
        $form['appbundle_user[plainPassword][second]'] = 'WebTestUserNew';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[POST /users/create] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserNew',
        ));
        $this->assertNotNull($testUser);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#user_show_titulo')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#user_list_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'user_list_titulo'");
    }

    public function testWebShow()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserNew',
        ));
        $crawler = $this->client->request('GET', '/users/' . $testUser->getId());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#user_show_titulo')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_titulo'");
        $this->assertEquals(1, $crawler->filter('#user_show_edit')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_edit'");
        $this->assertEquals(1, $crawler->filter('#user_show_remove')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_remove'");
        $this->assertEquals(1, $crawler->filter('#user_show_salir')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_salir'");
        $linkEdit = $crawler->filter('#user_show_edit')->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#user_edit_titulo')->count(), "[GET /users/{id}/edit] Elemento html no encotrado: 'user_edit_titulo'");
        $linkRemove = $crawler->filter('#user_show_remove')->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#user_remove_titulo')->count(), "[GET /users/{id}/remove] Elemento html no encotrado: 'user_remove_titulo'");
        $linkSalir = $crawler->filter('#user_show_salir')->link();
        $crawlerSalir = $this->client->click($linkSalir);
        $this->assertEquals(1, $crawlerSalir->filter('#user_list_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'user_list_titulo'");
    }

    public function testWebErrorCreateValidation()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/new] StatusCode inesperado");
        $form = $crawler->filter('#user_new_aceptar')->form();
        $form['appbundle_user[email]'] = 'webtestusernew@test';
        $form['appbundle_user[username]'] = 'WebTestUserNew';
        $form['appbundle_user[plainPassword][first]'] = 'WebTestUserNew';
        $form['appbundle_user[plainPassword][second]'] = 'WebTestUserNew';
        $crawlerSubmit = $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), "[POST /users/create] StatusCode inesperado");
        $this->assertGreaterThan(0, $crawlerSubmit->filter('.alert-danger')->count(), "[GET /users/create] Clase html no encontrada: 'alert-danger'");
    }

    public function testWebList()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#user_list_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'user_list_titulo'");
        $this->assertEquals(1, $crawler->filter('#user_list_new')->count(), "[GET /users/new] Elemento html no encotrado: 'user_list_new'");
        $this->assertGreaterThan(0, $crawler->filter('#user_list_table tr td .table_accion_show')->count(), "[GET /users/] Elemento html no encotrado: 'table_accion_show'");
        $this->assertGreaterThan(0, $crawler->filter('#user_list_table tr td .table_accion_edit')->count(), "[GET /users/] Elemento html no encotrado: 'table_accion_edit'");
        $this->assertGreaterThan(0, $crawler->filter('#user_list_table tr td .table_accion_remove')->count(), "[GET /users/] Elemento html no encotrado: 'table_accion_remove'");
        $linkNew = $crawler->filter('#user_list_new')->link();
        $crawlerNew = $this->client->click($linkNew);
        $this->assertEquals(1, $crawlerNew->filter('#user_new_titulo')->count(), "[GET /users/new] Elemento html no encotrado: 'user_new_titulo'");
        $linkShow = $crawler->filter('#user_list_table tr td .table_accion_show')->eq(0)->link();
        $crawlerShow = $this->client->click($linkShow);
        $this->assertEquals(1, $crawlerShow->filter('#user_show_titulo')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_titulo'");
        $linkEdit = $crawler->filter('#user_list_table tr td .table_accion_edit')->eq(0)->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#user_edit_titulo')->count(), "[GET /users/{id}/edit] Elemento html no encotrado: 'user_edit_titulo'");
        $linkRemove = $crawler->filter('#user_list_table tr td .table_accion_remove')->eq(0)->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#user_remove_titulo')->count(), "[GET /users/{id}/remove] Elemento html no encotrado: 'user_remove_titulo'");
    }

    public function testWebEdit()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserNew',
        ));
        $crawler = $this->client->request('GET', '/users/' . $testUser->getId() . '/edit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/{id}/edit] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#user_edit_titulo')->count(), "[GET /users/{id}/edit] Elemento html no encotrado: 'user_edit_titulo'");
        $this->assertEquals(1, $crawler->filter('#user_edit_aceptar')->count(), "[GET /users/{id}/edit] Elemento html no encotrado: 'user_edit_aceptar'");
        $this->assertEquals(1, $crawler->filter('#user_edit_cancelar')->count(), "[GET /users/{id}/edit] Elemento html no encotrado: 'user_edit_cancelar'");
        $form = $crawler->filter('#user_edit_aceptar')->form();
        $linkCancelar = $crawler->filter('#user_edit_cancelar')->link();
        $form['appbundle_user[email]'] = 'webtestuseredit@test';
        $form['appbundle_user[username]'] = 'WebTestUserEdit';
        $form['appbundle_user[plainPassword][first]'] = 'WebTestUserEdit';
        $form['appbundle_user[plainPassword][second]'] = 'WebTestUserEdit';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[PUT /users/update/{id}] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserEdit',
        ));
        $this->assertNotNull($testUser);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#user_show_titulo')->count(), "[GET /users/{id}] Elemento html no encotrado: 'user_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#user_list_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'user_list_titulo'");
    }

    public function testWebRemove()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserEdit',
        ));
        $crawler = $this->client->request('GET', '/users/' . $testUser->getId() .'/remove');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/{id}/remove] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#user_remove_aceptar')->count(), "[GET /users/{id}remove] Elemento html no encotrado: 'user_remove_aceptar'");
        $this->assertEquals(1, $crawler->filter('#user_remove_cancelar')->count(), "[GET /users/{id}/remove] Elemento html no encotrado: 'user_remove_cancelar'");
        $form = $crawler->filter('#user_remove_aceptar')->form();
        $linkCancelar = $crawler->filter('#user_remove_cancelar')->link();
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[DELETE /users/íd}] StatusCode inesperado");

        $testUser = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => 'WebTestUserEdit',
        ));
        $this->assertNull($testUser);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#user_list_titulo')->count(), "[GET /users] Elemento html no encotrado: 'user_list_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#user_list_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'user_list_titulo'");
    }

    public function testWebErrorAccesoDenegado()
    {
        $this->logIn(array('ROLE_USER'));
        $crawler = $this->client->request('GET', '/users/');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode(), "[GET /users/] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#error_titulo')->count(), "[GET /users/] Elemento html no encotrado: 'error_titulo'");
    }

    public function testWebErrorNoEncontrado()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/-1');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), "[GET /users/-1] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#error_titulo')->count(), "[GET /users/-1] Elemento html no encotrado: 'error_titulo'");
    }
}
