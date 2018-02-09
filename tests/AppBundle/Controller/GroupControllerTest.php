<?php

namespace AppBundle\Tests\Controller;

use Tests\AppTestCase;

class GroupControllerTest extends AppTestCase
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
        $crawler = $this->client->request('GET', '/groups/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/new] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#group_new_titulo')->count(), "[GET /groups/new] Elemento html no encotrado: 'group_new_titulo'");
        $this->assertEquals(1, $crawler->filter('#group_new_aceptar')->count(), "[GET /groups/new] Elemento html no encotrado: 'group_new_aceptar'");
        $this->assertEquals(1, $crawler->filter('#group_new_cancelar')->count(), "[GET /groups/new] Elemento html no encotrado: 'group_new_cancelar'");
        $form = $crawler->filter('#group_new_aceptar')->form();
        $linkCancelar = $crawler->filter('#group_new_cancelar')->link();
        $form['appbundle_group[name]'] = 'WebTestGroupNew';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[POST /groups/create] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupNew');
        $this->assertNotNull($testGroup);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#group_show_titulo')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#group_list_titulo')->count(), "[GET /groups/] Elemento html no encotrado: 'group_list_titulo'");
    }

    public function testWebShow()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupNew');
        $crawler = $this->client->request('GET', '/groups/' . $testGroup->getId());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#group_show_titulo')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_titulo'");
        $this->assertEquals(1, $crawler->filter('#group_show_edit')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_edit'");
        $this->assertEquals(1, $crawler->filter('#group_show_remove')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_remove'");
        $this->assertEquals(1, $crawler->filter('#group_show_salir')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_salir'");
        $linkEdit = $crawler->filter('#group_show_edit')->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#group_edit_titulo')->count(), "[GET /groups/{id}/edit] Elemento html no encotrado: 'group_edit_titulo'");
        $linkRemove = $crawler->filter('#group_show_remove')->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#group_remove_titulo')->count(), "[GET /groups/{id}/remove] Elemento html no encotrado: 'group_remove_titulo'");
        $linkSalir = $crawler->filter('#group_show_salir')->link();
        $crawlerSalir = $this->client->click($linkSalir);
        $this->assertEquals(1, $crawlerSalir->filter('#group_list_titulo')->count(), "[GET /groups/] Elemento html no encotrado: 'group_list_titulo'");
    }

    public function testWebList()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/groups/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#group_list_titulo')->count(), "[GET /groups/] Elemento html no encotrado: 'group_list_titulo'");
        $this->assertEquals(1, $crawler->filter('#group_list_new')->count(), "[GET /groups/new] Elemento html no encotrado: 'group_list_new'");
        $this->assertGreaterThan(0, $crawler->filter('#group_list_table tr td .table_accion_show')->count(), "[GET /groups/] Elemento html no encotrado: 'table_accion_show'");
        $this->assertGreaterThan(0, $crawler->filter('#group_list_table tr td .table_accion_edit')->count(), "[GET /groups/] Elemento html no encotrado: 'table_accion_edit'");
        $this->assertGreaterThan(0, $crawler->filter('#group_list_table tr td .table_accion_remove')->count(), "[GET /groups/] Elemento html no encotrado: 'table_accion_remove'");
        $linkNew = $crawler->filter('#group_list_new')->link();
        $crawlerNew = $this->client->click($linkNew);
        $this->assertEquals(1, $crawlerNew->filter('#group_new_titulo')->count(), "[GET /groups/new] Elemento html no encotrado: 'group_new_titulo'");
        $linkShow = $crawler->filter('#group_list_table tr td .table_accion_show')->eq(0)->link();
        $crawlerShow = $this->client->click($linkShow);
        $this->assertEquals(1, $crawlerShow->filter('#group_show_titulo')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_titulo'");
        $linkEdit = $crawler->filter('#group_list_table tr td .table_accion_edit')->eq(0)->link();
        $crawlerEdit = $this->client->click($linkEdit);
        $this->assertEquals(1, $crawlerEdit->filter('#group_edit_titulo')->count(), "[GET /groups/{id}/edit] Elemento html no encotrado: 'group_edit_titulo'");
        $linkRemove = $crawler->filter('#group_list_table tr td .table_accion_remove')->eq(0)->link();
        $crawlerRemove = $this->client->click($linkRemove);
        $this->assertEquals(1, $crawlerRemove->filter('#group_remove_titulo')->count(), "[GET /groups/{id}/remove] Elemento html no encotrado: 'group_remove_titulo'");
    }

    public function testWebEdit()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupNew');
        $crawler = $this->client->request('GET', '/groups/' . $testGroup->getId() . '/edit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/{id}/edit] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#group_edit_titulo')->count(), "[GET /groups/{id}/edit] Elemento html no encotrado: 'group_edit_titulo'");
        $this->assertEquals(1, $crawler->filter('#group_edit_aceptar')->count(), "[GET /groups/{id}/edit] Elemento html no encotrado: 'group_edit_aceptar'");
        $this->assertEquals(1, $crawler->filter('#group_edit_cancelar')->count(), "[GET /groups/{id}/edit] Elemento html no encotrado: 'group_edit_cancelar'");
        $form = $crawler->filter('#group_edit_aceptar')->form();
        $linkCancelar = $crawler->filter('#group_edit_cancelar')->link();
        $form['appbundle_group[name]'] = 'WebTestGroupEdit';
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[PUT /groups/update/{id}] StatusCode inesperado");

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupEdit');
        $this->assertNotNull($testGroup);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/{id}] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#group_show_titulo')->count(), "[GET /groups/{id}] Elemento html no encotrado: 'group_show_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#group_list_titulo')->count(), "[GET /groups/] Elemento html no encotrado: 'group_list_titulo'");
    }

    public function testWebRemove()
    {
        $this->logIn();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupEdit');
        $crawler = $this->client->request('GET', '/groups/' . $testGroup->getId() .'/remove');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/{id}/remove] StatusCode inesperado");
        $this->assertEquals(1, $crawler->filter('#group_remove_aceptar')->count(), "[GET /groups/{id}remove] Elemento html no encotrado: 'group_remove_aceptar'");
        $this->assertEquals(1, $crawler->filter('#group_remove_cancelar')->count(), "[GET /groups/{id}/remove] Elemento html no encotrado: 'group_remove_cancelar'");
        $form = $crawler->filter('#group_remove_aceptar')->form();
        $linkCancelar = $crawler->filter('#group_remove_cancelar')->link();
        $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "[DELETE /groups/íd}] StatusCode inesperado");

        $testGroup = $em->getRepository('AppBundle:Group')->findOneByName('WebTestGroupEdit');
        $this->assertNull($testGroup);

        $crawlerAceptar = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerAceptar->filter('#group_list_titulo')->count(), "[GET /groups] Elemento html no encotrado: 'group_list_titulo'");
        $crawlerCancelar = $this->client->click($linkCancelar);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "[GET /groups/] StatusCode inesperado");
        $this->assertEquals(1, $crawlerCancelar->filter('#group_list_titulo')->count(), "[GET /groups/] Elemento html no encotrado: 'group_list_titulo'");
    }
}
