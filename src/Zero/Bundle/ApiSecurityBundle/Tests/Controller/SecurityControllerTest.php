<?php

namespace Zero\Bundle\ApiSecurityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/oauth/v2/auth_login');
    }

    public function testLogincheck()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/oauth/v2/auth_login_check');
    }

}
