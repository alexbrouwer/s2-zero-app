<?php


namespace Zero\Bundle\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Zero\Base\Test\WebTestCase;

class UserGroupControllerTest extends WebTestCase
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
        $this->jsonRequest($this->client, 'GET', '/api/users/groups');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('groups', $content);
        $this->assertCount(2, $content['groups']);
    }

    public function testGet()
    {
        $this->jsonRequest($this->client, 'GET', '/api/users/groups/1');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('group', $content);
        $this->assertArrayHasKey('id', $content['group']);
        $this->assertEquals(1, $content['group']['id']);
    }

    public function testPost()
    {
        $data = array(
            'user_group' => array(
                'name'  => 'createdgroup',
                'roles' => array('ROLE1', 'ROLE2')
            )
        );
        $this->jsonRequest($this->client, 'POST', '/api/users/groups', $data);

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
            'user_group' => array(
                'name'  => 'updatedgroup',
                'roles' => array('ROLE3', 'ROLE2')
            )
        );
        $this->jsonRequest($this->client, 'PUT', '/api/users/groups/1', $data);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }

    public function testPatch()
    {
        $data = array(
            'user_group' => array(
                'name'  => 'patchedgroup',
                'roles' => array('ROLE3', 'ROLE2')
            )
        );
        $this->jsonRequest($this->client, 'PATCH', '/api/users/groups/1', $data);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderExists($response, 'Location');
    }

    public function testDelete()
    {
        $this->jsonRequest($this->client, 'DELETE', '/api/users/groups/1');

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $content = json_decode($response->getContent(), true);

        $this->assertNull($content);
        $this->assertHeaderNotExists($response, 'Location');

        $this->jsonRequest($this->client, 'GET', '/api/users/groups/1');
        $this->assertJsonResponse($this->client->getResponse(), 404);
    }
}
 