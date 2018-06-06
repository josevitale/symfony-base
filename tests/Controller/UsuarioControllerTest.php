<?php

namespace App\Tests\Controller;

use App\Tests\AppTestCase;

class UsuarioControllerTest extends AppTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->crearUsuario('test');
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->eliminarUsuario('test');
    }

    public function testWebNew()
    {
        $this->logInWeb();
        $crawler = $this->client->request('GET', '/usuarios/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/new] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#usuario_new_titulo')->count(), "[GET /usuarios/new] Elemento html no encotrado: 'usuario_new_titulo'");
        $this->assertEquals(1, $crawler->filter('#usuario_new_aceptar')->count(), "[GET /usuarios/new] Elemento html no encotrado: 'usuario_new_aceptar'");
        $this->assertEquals(1, $crawler->filter('#usuario_new_cancelar')->count(), "[GET /usuarios/new] Elemento html no encotrado: 'usuario_new_cancelar'");
        $form = $crawler->filter('#usuario_new_aceptar')->form();
        $linkCancelar = $crawler->filter('#usuario_new_cancelar')->link();
        $form['app_usuario[email]'] = 'webtestusuarionew@test';
        $form['app_usuario[username]'] = 'WebTestUsuarioNew';
        $form['app_usuario[plainPassword][first]'] = 'WebTestUsuarioNew';
        $form['app_usuario[plainPassword][second]'] = 'WebTestUsuarioNew';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[POST /usuarios/create] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioNew',
        ));
        $this->assertNotNull($testUsuario);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#usuario_show_titulo')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#usuario_list_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'usuario_list_titulo'");
    }

    public function testWebShow()
    {
        $this->logInWeb();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioNew',
        ));
        $crawler = $this->client->request('GET', '/usuarios/' . $testUsuario->getId());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#usuario_show_titulo')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_titulo'");
        $this->assertEquals(1, $crawler->filter('#usuario_show_edit')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_edit'");
        $this->assertEquals(1, $crawler->filter('#usuario_show_remove')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_remove'");
        $this->assertEquals(1, $crawler->filter('#usuario_show_salir')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_salir'");
        $linkEdit = $crawler->filter('#usuario_show_edit')->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#usuario_edit_titulo')->count(), "[GET /usuarios/{id}/edit] Elemento html no encotrado: 'usuario_edit_titulo'");
        $linkRemove = $crawler->filter('#usuario_show_remove')->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#usuario_remove_titulo')->count(), "[GET /usuarios/{id}/remove] Elemento html no encotrado: 'usuario_remove_titulo'");
        $linkSalir = $crawler->filter('#usuario_show_salir')->link();
        $crawlerSalir = $this->client->click($linkSalir);
        $this->assertEquals(1, $crawlerSalir->filter('#usuario_list_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'usuario_list_titulo'");
    }

    public function testWebErrorCreateValidation()
    {
        $this->logInWeb();
        $crawler = $this->client->request('GET', '/usuarios/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/new] StatusCode inesperado");
        $form = $crawler->filter('#usuario_new_aceptar')->form();
        $form['app_usuario[email]'] = 'webtestusuarionew@test';
        $form['app_usuario[username]'] = 'WebTestUsuarioNew';
        $form['app_usuario[plainPassword][first]'] = 'WebTestUsuarioNew';
        $form['app_usuario[plainPassword][second]'] = 'WebTestUsuarioNew';
        $crawlerSubmit = $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), "[POST /usuarios/create] StatusCode inesperado");
        $this->assertGreaterThan(0, $crawlerSubmit->filter('.alert-danger')->count(), "[GET /usuarios/create] Clase html no encontrada: 'alert-danger'");
    }

    public function testWebList()
    {
        $this->logInWeb();
        $crawler = $this->client->request('GET', '/usuarios/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#usuario_list_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'usuario_list_titulo'");
        $this->assertEquals(1, $crawler->filter('#usuario_list_new')->count(), "[GET /usuarios/new] Elemento html no encotrado: 'usuario_list_new'");
        $this->assertGreaterThan(0, $crawler->filter('#usuario_list_table tr td .table_accion_show')->count(), "[GET /usuarios/] Elemento html no encotrado: 'table_accion_show'");
        $this->assertGreaterThan(0, $crawler->filter('#usuario_list_table tr td .table_accion_edit')->count(), "[GET /usuarios/] Elemento html no encotrado: 'table_accion_edit'");
        $this->assertGreaterThan(0, $crawler->filter('#usuario_list_table tr td .table_accion_remove')->count(), "[GET /usuarios/] Elemento html no encotrado: 'table_accion_remove'");
        $linkNew = $crawler->filter('#usuario_list_new')->link();
        $crawlerNew = $this->client->click($linkNew);
        $this->assertEquals(1, $crawlerNew->filter('#usuario_new_titulo')->count(), "[GET /usuarios/new] Elemento html no encotrado: 'usuario_new_titulo'");
        $linkShow = $crawler->filter('#usuario_list_table tr td .table_accion_show')->eq(0)->link();
        $crawlerShow = $this->client->click($linkShow);
        $this->assertEquals(1, $crawlerShow->filter('#usuario_show_titulo')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_titulo'");
        $linkEdit = $crawler->filter('#usuario_list_table tr td .table_accion_edit')->eq(0)->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#usuario_edit_titulo')->count(), "[GET /usuarios/{id}/edit] Elemento html no encotrado: 'usuario_edit_titulo'");
        $linkRemove = $crawler->filter('#usuario_list_table tr td .table_accion_remove')->eq(0)->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#usuario_remove_titulo')->count(), "[GET /usuarios/{id}/remove] Elemento html no encotrado: 'usuario_remove_titulo'");
    }

    public function testWebEdit()
    {
        $this->logInWeb();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioNew',
        ));
        $crawler = $this->client->request('GET', '/usuarios/' . $testUsuario->getId() . '/edit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}/edit] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#usuario_edit_titulo')->count(), "[GET /usuarios/{id}/edit] Elemento html no encotrado: 'usuario_edit_titulo'");
        $this->assertEquals(1, $crawler->filter('#usuario_edit_aceptar')->count(), "[GET /usuarios/{id}/edit] Elemento html no encotrado: 'usuario_edit_aceptar'");
        $this->assertEquals(1, $crawler->filter('#usuario_edit_cancelar')->count(), "[GET /usuarios/{id}/edit] Elemento html no encotrado: 'usuario_edit_cancelar'");
        $form = $crawler->filter('#usuario_edit_aceptar')->form();
        $linkCancelar = $crawler->filter('#usuario_edit_cancelar')->link();
        $form['app_usuario[email]'] = 'webtestusuarioedit@test';
        $form['app_usuario[username]'] = 'WebTestUsuarioEdit';
        $form['app_usuario[plainPassword][first]'] = 'WebTestUsuarioEdit';
        $form['app_usuario[plainPassword][second]'] = 'WebTestUsuarioEdit';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[PUT /usuarios/{id}] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioEdit',
        ));
        $this->assertNotNull($testUsuario);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#usuario_show_titulo')->count(), "[GET /usuarios/{id}] Elemento html no encotrado: 'usuario_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#usuario_list_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'usuario_list_titulo'");
    }

    public function testWebRemove()
    {
        $this->logInWeb();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioEdit',
        ));
        $crawler = $this->client->request('GET', '/usuarios/' . $testUsuario->getId() .'/remove');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}/remove] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#usuario_remove_aceptar')->count(), "[GET /usuarios/{id}remove] Elemento html no encotrado: 'usuario_remove_aceptar'");
        $this->assertEquals(1, $crawler->filter('#usuario_remove_cancelar')->count(), "[GET /usuarios/{id}/remove] Elemento html no encotrado: 'usuario_remove_cancelar'");
        $form = $crawler->filter('#usuario_remove_aceptar')->form();
        $linkCancelar = $crawler->filter('#usuario_remove_cancelar')->link();
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[DELETE /usuarios/íd}] StatusCode inesperado");

        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'WebTestUsuarioEdit',
        ));
        $this->assertNull($testUsuario);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#usuario_list_titulo')->count(), "[GET /usuarios] Elemento html no encotrado: 'usuario_list_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#usuario_list_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'usuario_list_titulo'");
    }

    public function testWebErrorAccesoDenegado()
    {
        $this->logInWeb(array('ROLE_USER'));
        $crawler = $this->client->request('GET', '/usuarios/');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#error_titulo')->count(), "[GET /usuarios/] Elemento html no encotrado: 'error_titulo'");
    }

    public function testWebErrorNoEncontrado()
    {
        $this->logInWeb();
        $crawler = $this->client->request('GET', '/usuarios/-1');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/-1] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#error_titulo')->count(), "[GET /usuarios/-1] Elemento html no encotrado: 'error_titulo'");
    }

    public function testJsonCreate()
    {
        $content = $this->logInJson();
        $data = json_encode(array(
            'email' => 'jsontestusuarionew@test',
            'username' => 'JsonTestUsuarioNew',
            'plainPassword' => array(
                'first' => 'JsonTestUsuarioNew',
                'second' => 'JsonTestUsuarioNew',
            ),
        ));
        $this->client->request('POST', '/usuarios/create', array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ), $data);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testJsonShow()
    {
        $content = $this->logInJson();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'JsonTestUsuarioNew',
        ));
        $this->client->request('GET', '/usuarios/' . $testUsuario->getId(), array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/{id}] StatusCode inesperado");
    }

    public function testJsonErrorCreateValidation()
    {
        $content = $this->logInJson();
        $data = json_encode(array(
            'email' => 'jsontestusuarionew@test',
            'username' => 'JsonTestUsuarioNew',
            'plainPassword' => array(
                'first' => 'JsonTestUsuarioNew',
                'second' => 'JsonTestUsuarioNew',
            ),
        ));
        $this->client->request('POST', '/usuarios/create', array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ), $data);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), "[POST /usuarios/create] StatusCode inesperado");
    }

    public function testJsonList()
    {
        $content = $this->logInJson();
        $this->client->request('GET', '/usuarios/', array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->client->request('GET', '/usuarios/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/] StatusCode inesperado");
    }

    public function testWebUpdate()
    {
        $content = $this->logInJson();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'JsonTestUsuarioNew',
        ));
        $data = json_encode(array(
            'email' => 'jsontestusuarioupdate@test',
            'username' => 'JsonTestUsuarioUpdate',
            'plainPassword' => array(
                'first' => 'JsonTestUsuarioUpdate',
                'second' => 'JsonTestUsuarioUpdate',
            ),
        ));
        $this->client->request('PUT', '/usuarios/' . $testUsuario->getId(), array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ), $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[PUT /usuarios/{id}] StatusCode inesperado");
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'JsonTestUsuarioUpdate',
        ));
        $this->assertNotNull($testUsuario);
    }

    public function testJsonDelete()
    {
        $content = $this->logInJson();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'JsonTestUsuarioUpdate',
        ));
        $this->client->request('DELETE', '/usuarios/' . $testUsuario->getId(), array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode(), "[DELETE /usuarios/íd}] StatusCode inesperado");

        $testUsuario = $em->getRepository('App:Usuario')->findOneBy(array(
            'username' => 'JsonTestUsuarioUpdate',
        ));
        $this->assertNull($testUsuario);
    }

    public function testJsonErrorNoEncontrado()
    {
        $content = $this->logInJson();
        $this->client->request('GET', '/usuarios/-1', array(), array(), array(
            'HTTP_AUTHORIZATION' => $content['token'],
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->client->request('GET', '/usuarios/-1');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), "[GET /usuarios/-1] StatusCode inesperado");
    }
}
