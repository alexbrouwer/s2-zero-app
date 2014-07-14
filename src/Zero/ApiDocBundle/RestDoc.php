<?php


namespace Zero\ApiDocBundle;

use Symfony\Component\Routing\Route;

class RestDoc
{

    /**
     * @var Route
     */
    private $route;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * Most of the time, a single line of text describing the action.
     * @var string
     */
    private $description;

    /**
     * Extended documentation.
     *
     * @var string|null
     */
    private $documentation;

    /**
     * @var bool
     */
    private $resource = false;

    /**
     * @var array
     */
    private $statusCodes = array();

    /**
     * @var array
     */
    private $requirements = array();

    /**
     * @var array
     */
    private $filters = array();

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $section;

    /**
     * @var array
     */
    private $response = array();

    /**
     * @var bool
     */
    private $https = false;

    /**
     * @var bool
     */
    private $authentication = false;

    /**
     * @var array
     */
    private $authenticationRoles = array();

    /**
     * @var int
     */
    private $cache;

    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @var array
     */
    private $tags = array();

    /**
     * Get Authentication
     *
     * @return boolean
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * Set authentication
     *
     * @param boolean $authentication
     *
     * @return RestDoc
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;

        return $this;
    }

    /**
     * Get AuthenticationRoles
     *
     * @return array
     */
    public function getAuthenticationRoles()
    {
        return $this->authenticationRoles;
    }

    /**
     * Set authenticationRoles
     *
     * @param array $authenticationRoles
     *
     * @return RestDoc
     */
    public function setAuthenticationRoles($authenticationRoles)
    {
        $this->authenticationRoles = $authenticationRoles;

        return $this;
    }

    /**
     * Get Cache
     *
     * @return int
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set cache
     *
     * @param int $cache
     *
     * @return RestDoc
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get Deprecated
     *
     * @return boolean
     */
    public function isDeprecated()
    {
        return $this->deprecated;
    }

    /**
     * Set deprecated
     *
     * @param boolean $deprecated
     *
     * @return RestDoc
     */
    public function setDeprecated($deprecated)
    {
        $this->deprecated = $deprecated;

        return $this;
    }

    /**
     * Get Filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Add filter
     *
     * @param string $name
     * @param array $options
     *
     * @return RestDoc
     */
    public function addFilter($name, array $options)
    {
        $this->filters[$name] = $options;

        return $this;
    }

    /**
     * Get Https
     *
     * @return boolean
     */
    public function getHttps()
    {
        return $this->https;
    }

    /**
     * Set https
     *
     * @param boolean $https
     *
     * @return RestDoc
     */
    public function setHttps($https)
    {
        $this->https = $https;

        return $this;
    }

    /**
     * Get Input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set input
     *
     * @param string $input
     *
     * @return RestDoc
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get Link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return RestDoc
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set method
     *
     * @param string $method
     *
     * @return RestDoc
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get Output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set output
     *
     * @param string $output
     *
     * @return RestDoc
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get Parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Add parameter
     *
     * @param string $name
     * @param array $options
     *
     * @return RestDoc
     */
    public function addParameter($name, array $options)
    {
        $this->parameters[$name] = $options;

        return $this;
    }

    /**
     * Get Requirements
     *
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Add requirement
     *
     * @param string $name
     * @param array $options
     *
     * @return RestDoc
     */
    public function addRequirement($name, array $options)
    {
        $this->requirements[$name] = $options;

        return $this;
    }

    /**
     * Get Response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param array $response
     *
     * @return RestDoc
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get Section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set section
     *
     * @param string $section
     *
     * @return RestDoc
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get Tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     *
     * @param array $tags
     *
     * @return RestDoc
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return RestDoc
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get Documentation
     *
     * @return null|string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Set documentation
     *
     * @param null|string $documentation
     *
     * @return RestDoc
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;

        return $this;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return RestDoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set isResource
     *
     * @param boolean $isResource
     *
     * @return RestDoc
     */
    public function setResource($isResource)
    {
        $this->resource = $isResource;

        return $this;
    }

    /**
     * Check if route is to a resource or an action
     *
     * @return bool
     */
    public function isResource()
    {
        return $this->resource;
    }

    /**
     * Get StatusCodes
     *
     * @return array
     */
    public function getStatusCodes()
    {
        return $this->statusCodes;
    }

    /**
     * Add statusCode
     *
     * @param int $code
     * @param string $description
     *
     * @return RestDoc
     */
    public function addStatusCode($code, $description)
    {
        if (!array_key_exists($code, $this->statusCodes)) {
            $this->statusCodes[$code] = array();
        }

        $this->statusCodes[$code][] = $description;

        return $this;
    }

    /**
     * Get Route
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set route
     *
     * @param Route $route
     *
     * @return RestDoc
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;

        if (is_callable(array($route, 'getHost'))) {
            $this->host = $route->getHost() ?: null;
        } else {
            $this->host = null;
        }

        $this->uri    = $route->getPath();
        $this->method = $route->getRequirement('_method') ?: 'ANY';

        return $this;
    }
}