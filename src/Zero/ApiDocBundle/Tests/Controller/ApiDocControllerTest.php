<?php


namespace Zero\ApiDocBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiDocControllerTest extends WebTestCase
{
    public function testDefaultAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/docs/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
 