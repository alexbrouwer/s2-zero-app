<?php

namespace Zero\Bundle\ApiSecurityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorizeControllerTest extends WebTestCase
{
    public function testAuthorize()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/authorize');
    }

}
