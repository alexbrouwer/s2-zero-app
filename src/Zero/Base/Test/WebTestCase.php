<?php


namespace Zero\Base\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends FixturesTestCase
{
    /**
     * @param Client $client
     * @param $verb
     * @param array $endpoint
     * @param array $data
     *
     * @return Crawler
     */
    protected function jsonRequest(Client $client, $verb, $endpoint, array $data = array())
    {
        $data = empty($data) ? null : json_encode($data);

        return $client->request(
            $verb,
            $endpoint,
            array(),
            array(),
            array(
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ),
            $data
        );
    }

    /**
     * Checks if response is a valid JSON response
     *
     * @param Response $response
     * @param int $statusCode
     *
     * @return void
     */
    protected function assertJsonResponse(Response $response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }

    /**
     * @param Response $response
     * @param $header
     */
    protected function assertHeaderExists(Response $response, $header)
    {
        $this->assertTrue($response->headers->has($header), sprintf('Response does not contain header "%s"', $header));
    }

    /**
     * @param Response $response
     * @param $header
     */
    protected function assertHeaderNotExists(Response $response, $header)
    {
        $this->assertFalse($response->headers->has($header), sprintf('Response contains header "%s"', $header));
    }

    /**
     * @param Response $response
     * @param $headerKey
     * @param $headerValue
     */
    protected function assertHeaderEquals(Response $response, $headerKey, $headerValue)
    {
        $this->assertTrue(
            $response->headers->contains($headerKey, $headerValue),
            sprintf('Response does not contain header "%s" with "%s"', $headerKey, $headerValue)
        );
    }

    /**
     * @param Response $response
     * @param $headerKey
     * @param $headerValue
     */
    protected function assertHeaderNotEquals(Response $response, $headerKey, $headerValue)
    {
        $this->assertFalse(
            $response->headers->contains($headerKey, $headerValue),
            sprintf('Response contains header "%s" with "%s"', $headerKey, $headerValue)
        );
    }

    /**
     * Checks the success state of a response
     *
     * @param Response $response Response object
     * @param bool $success to define whether the response is expected to be successful
     * @param string $type
     *
     * @return void
     */
    protected function assertSuccessfulResponse(Response $response, $success = true, $type = 'text/html')
    {
        try {
            $crawler = new Crawler();
            $crawler->addContent($response->getContent(), $type);
            if (!count($crawler->filter('title'))) {
                $title = '[' . $response->getStatusCode() . '] - ' . $response->getContent();
            } else {
                $title = $crawler->filter('title')->text();
            }
        } catch (\Exception $e) {
            $title = $e->getMessage();
        }

        if ($success) {
            $this->assertTrue($response->isSuccessful(), 'The Response was not successful: ' . $title);
        } else {
            $this->assertFalse($response->isSuccessful(), 'The Response was successful: ' . $title);
        }
    }
}