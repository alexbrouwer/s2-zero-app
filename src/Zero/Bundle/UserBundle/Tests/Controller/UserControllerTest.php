<?php


namespace Zero\Bundle\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Zero\Bundle\ApiBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = $this->createClient(array(), array('HTTPS' => true));

        $this->loadFixtures(
            array(
                'Zero\Bundle\UserBundle\Tests\Fixtures\ORM\LoadData'
            )
        );
    }

    public function testGetAll()
    {
        $this->jsonRequest($this->client, 'GET', '/api/users');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('users', $content);
        $this->assertCount(2, $content['users']);
    }

    public function testGet()
    {
        $this->jsonRequest($this->client, 'GET', '/api/users/1');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('user', $content);
        $this->assertArrayHasKey('id', $content['user']);
        $this->assertEquals(1, $content['user']['id']);
    }

    public function testPost()
    {
        $data = array(
            'user' => array(
                'username'    => 'createduser',
                'email'       => 'createduser@example.com',
                'displayName' => 'Created User',
                'groups'      => array(2)
            )
        );
        $this->jsonRequest($this->client, 'POST', '/api/users', $data);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 201);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');

        $this->jsonRequest($this->client, 'GET', $response->headers->get('Location'));
        $this->assertJsonResponse($this->client->getResponse());
    }

    public function testPut()
    {
        $data = array(
            'user' => array(
                'username'    => 'updateuser',
                'email'       => 'updateuser@example.com',
                'displayName' => 'Update User',
                'groups'      => array(1)
            )
        );
        $this->jsonRequest($this->client, 'PUT', '/api/users/1', $data);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }

    public function testPatch()
    {
        $data = array(
            'user' => array(
                'username'    => 'patcheduser'
            )
        );
        $this->jsonRequest($this->client, 'PATCH', '/api/users/1', $data);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }

    public function testDelete()
    {
        $this->jsonRequest($this->client, 'DELETE', '/api/users/1');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderNotExists($response, 'Location');

        $this->jsonRequest($this->client, 'GET', '/api/users/1');
        $this->assertJsonResponse($this->client->getResponse(), 404);
    }

    public function testGetGroups()
    {
        $this->jsonRequest($this->client, 'GET', '/api/users/1/groups');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('groups', $content);
        $this->assertCount(1, $content['groups']);
        $this->assertArrayHasKey('id', $content['groups'][0]);
        $this->assertEquals(1, $content['groups'][0]['id']);
    }

    public function testLinkGroup()
    {
        $this->jsonRequest($this->client, 'LINK', '/api/users/1/groups/2');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }

    public function testUnlinkGroup()
    {
        $this->jsonRequest($this->client, 'UNLINK', '/api/users/1/groups/2');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }
}
 