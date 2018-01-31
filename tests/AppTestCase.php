<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppTestCase extends WebTestCase
{
    private static $staticClient;
    protected $client;

    public static function setUpBeforeClass()
    {
        self::$staticClient = static::createClient();
    }

    protected function setUp()
    {
        $this->client = self::$staticClient;
    }
}
