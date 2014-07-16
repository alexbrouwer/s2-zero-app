<?php


namespace Zero\Bundle\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiDocControllerTest extends WebTestCase
{
    public function testDefaultAction()
    {
        $client = static::createClient();

        $client->request('GET', '/docs/api/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
 