<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zero\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Formatter\HtmlFormatter as BaseHtmlFormatter;
use Symfony\Component\Templating\EngineInterface;

class HtmlFormatter extends BaseHtmlFormatter
{

    /**
     * @var string
     */
    protected $apiName;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $defaultRequestFormat;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var boolean
     */
    private $enableSandbox;

    /**
     * @var array
     */
    private $requestFormats;

    /**
     * @var string
     */
    private $requestFormatMethod;

    /**
     * @var string
     */
    private $acceptType;

    /**
     * @var string
     */
    private $bodyFormat;

    /**
     * @var array
     */
    private $authentication;

    /**
     * @var string
     */
    private $motdTemplate;

    /**
     * @param array $authentication
     */
    public function setAuthentication(array $authentication = null)
    {
        $this->authentication = $authentication;
    }

    /**
     * @param string $apiName
     */
    public function setApiName($apiName)
    {
        $this->apiName = $apiName;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param boolean $enableSandbox
     */
    public function setEnableSandbox($enableSandbox)
    {
        $this->enableSandbox = $enableSandbox;
    }

    /**
     * @param EngineInterface $engine
     */
    public function setTemplatingEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param string $acceptType
     */
    public function setAcceptType($acceptType)
    {
        $this->acceptType = $acceptType;
    }

    /**
     * @param string $bodyFormat
     */
    public function setBodyFormat($bodyFormat)
    {
        $this->bodyFormat = $bodyFormat;
    }

    /**
     * @param string $method
     */
    public function setRequestFormatMethod($method)
    {
        $this->requestFormatMethod = $method;
    }

    /**
     * @param array $formats
     */
    public function setRequestFormats(array $formats)
    {
        $this->requestFormats = $formats;
    }

    /**
     * @param string $format
     */
    public function setDefaultRequestFormat($format)
    {
        $this->defaultRequestFormat = $format;
    }

    /**
     * @param string $motdTemplate
     */
    public function setMotdTemplate($motdTemplate)
    {
        $this->motdTemplate = $motdTemplate;
    }

    /**
     * @return string
     */
    public function getMotdTemplate()
    {
        return $this->motdTemplate;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        return $this->engine->render(
            'ZeroApiDocBundle:ApiDoc:index.html.twig',
            array_merge(
                array(
                    'data'           => $data,
                    'displayContent' => true,
                ),
                $this->getGlobalVars()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        return $this->engine->render(
            'ZeroApiDocBundle:ApiDoc:index.html.twig',
            array(
                'data' =>
                    array_merge(
                        array(
                            'sections' => $collection,
                        ),
                        $this->getGlobalVars()
                    )
            )
        );
    }

    protected function processAnnotation($annotation)
    {
        $annotation = parent::processAnnotation($annotation);

        $uri              = ltrim($annotation['uri'], '/');
        $annotation['id'] = str_replace(array('/', '.', '{', '}'), array('-', '-', ''), $uri) . '-' . strtolower($annotation['method']);

        return $annotation;
    }

    /**
     * @return array
     */
    private function getGlobalVars()
    {
        return array(
            'apiName'              => $this->apiName,
            'authentication'       => $this->authentication,
            'endpoint'             => $this->endpoint,
            'enableSandbox'        => $this->enableSandbox,
            'requestFormatMethod'  => $this->requestFormatMethod,
            'acceptType'           => $this->acceptType,
            'bodyFormat'           => $this->bodyFormat,
            'requestFormats'       => $this->requestFormats,
            'defaultRequestFormat' => $this->defaultRequestFormat,
            'date'                 => date(DATE_RFC822),
            'motdTemplate'         => $this->motdTemplate
        );
    }
}