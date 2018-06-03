<?php

namespace App\Tests\Controller;

use App\Tests\AppTestCase;

class DefaultControllerTest extends AppTestCase
{
    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }
}
