<?php


namespace Zero\ApiDocBundle\Tests\Listener;

use Zero\ApiDocBundle\Annotation\Api;
use Zero\ApiDocBundle\Annotation\Deprecated;
use Zero\ApiDocBundle\Annotation\Description;
use Zero\ApiDocBundle\Annotation\Filter;
use Zero\ApiDocBundle\Annotation\Https;
use Zero\ApiDocBundle\Annotation\Input;
use Zero\ApiDocBundle\Annotation\Output;
use Zero\ApiDocBundle\Annotation\Parameter;
use Zero\ApiDocBundle\Annotation\Requirement;
use Zero\ApiDocBundle\Annotation\Resource;
use Zero\ApiDocBundle\Annotation\Section;
use Zero\ApiDocBundle\Annotation\StatusCode;
use Zero\ApiDocBundle\Annotation\Tag;
use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Listener\ApiDocAnnotationsListener;
use Zero\ApiDocBundle\RestDoc;

class ApiDocAnnotationsListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ApiDocAnnotationsListener
     */
    private $listener;

    /**
     * @var RestDoc
     */
    private $container;

    /**
     * @var \ReflectionMethod|\Mockery\MockInterface
     */
    private $method;

    /**
     * @var \Symfony\Component\Routing\Route|\Mockery\MockInterface
     */
    private $route;

    public function setUp()
    {
        $this->listener  = new ApiDocAnnotationsListener();
        $this->container = new RestDoc();
        $this->method    = \Mockery::mock('\ReflectionMethod');
        $this->route     = \Mockery::mock('Symfony\Component\Routing\Route');
    }

    public function getExtractorEvent(array $annotations)
    {
        return new ExtractorEvent($this->container, $this->method, $this->route, $annotations);
    }

    public function testDoesNotHandleUnknownAnnotations()
    {
        $expected = new RestDoc;

        $unknownAnnotation = \Mockery::mock('UnknownAnnotation');
        $event             = $this->getExtractorEvent(array($unknownAnnotation));
        $event->setContainer($expected);
        $this->listener->onExtractorHandle($event);

        $this->assertEquals($this->container, $event->getContainer());
    }

    /**
     * @expectedException \Zero\ApiDocBundle\Exception\UnknownAnnotationException
     */
    public function testThrowsExceptionForInvalidAnnotations()
    {
        $expected = new RestDoc;

        $unknownAnnotation = \Mockery::mock('Zero\ApiDocBundle\Annotation\AnnotationInterface');
        $event             = $this->getExtractorEvent(array($unknownAnnotation));
        $event->setContainer($expected);
        $this->listener->onExtractorHandle($event);
    }

    public function testHandleApiAnnotation()
    {
        $expected = new RestDoc;

        $annotation = new Api();
        $event      = $this->getExtractorEvent(array($annotation));
        $event->setContainer($expected);
        $this->listener->onExtractorHandle($event);

        $this->assertEquals($this->container, $event->getContainer());
    }

    public function testHandleDeprecatedAnnotation()
    {
        $annotation = new Deprecated();
        $event      = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertTrue($this->container->isDeprecated());

        $annotation        = new Deprecated();
        $annotation->value = false;
        $event             = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertFalse($this->container->isDeprecated());
    }

    public function testHandleDescriptionAnnotation()
    {
        $expected = 'test description';

        $annotation        = new Description();
        $annotation->value = $expected;
        $event             = $this->getExtractorEvent(array($annotation));

        $this->listener->onExtractorHandle($event);

        $this->assertEquals($expected, $this->container->getDescription());
    }

    public function testHandleFilterAnnotation()
    {
        $annotationOne       = new Filter();
        $annotationOne->name = 'filter1';

        $annotationTwo           = new Filter();
        $annotationTwo->name     = 'filter2';
        $annotationTwo->dataType = 'integer';
        $annotationTwo->pattern  = '/^\d+$/';
        $event                   = $this->getExtractorEvent(array($annotationOne, $annotationTwo));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getFilters();

        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($annotationOne->name, $actual);
        $this->assertEquals(array('dataType' => 'string', 'pattern' => null), $actual['filter1']);

        $this->assertArrayHasKey($annotationTwo->name, $actual);
        $this->assertEquals(array('dataType' => 'integer', 'pattern' => '/^\d+$/'), $actual['filter2']);
    }

    public function testHandleHttpsAnnotation()
    {
        $annotation = new Https();
        $event      = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertTrue($this->container->isHttps());

        $annotation        = new Https();
        $annotation->value = false;
        $event             = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertFalse($this->container->isHttps());
    }

    public function testHandleInputAnnotation()
    {
        $expected = array('class' => 'someClass', 'groups' => array('group1'));

        $annotation         = new Input();
        $annotation->class  = $expected['class'];
        $annotation->groups = $expected['groups'];
        $event              = $this->getExtractorEvent(array($annotation));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getInput();

        $this->assertEquals($expected, $actual);
    }

    public function testHandleOutputAnnotation()
    {
        $expected = array('class' => 'someClass', 'groups' => array('group1'));

        $annotation         = new Output();
        $annotation->class  = $expected['class'];
        $annotation->groups = $expected['groups'];
        $event              = $this->getExtractorEvent(array($annotation));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getOutput();

        $this->assertEquals($expected, $actual);
    }

    public function testHandleParameterAnnotation()
    {
        $annotationOne       = new Parameter();
        $annotationOne->name = 'parameter1';

        $annotationTwo              = new Parameter();
        $annotationTwo->name        = 'parameter2';
        $annotationTwo->dataType    = 'integer';
        $annotationTwo->description = 'description';
        $annotationTwo->required    = true;
        $event                      = $this->getExtractorEvent(array($annotationOne, $annotationTwo));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getParameters();

        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($annotationOne->name, $actual);
        $this->assertEquals(array('dataType' => 'string', 'required' => false, 'description' => null), $actual['parameter1']);

        $this->assertArrayHasKey($annotationTwo->name, $actual);
        $this->assertEquals(array('dataType' => 'integer', 'required' => true, 'description' => 'description'), $actual['parameter2']);
    }

    public function testHandleRequirementAnnotation()
    {
        $annotationOne       = new Requirement();
        $annotationOne->name = 'requirement1';

        $annotationTwo              = new Requirement();
        $annotationTwo->name        = 'requirement2';
        $annotationTwo->dataType    = 'integer';
        $annotationTwo->description = 'description';
        $annotationTwo->requirement = '/^\d+$/';
        $event                      = $this->getExtractorEvent(array($annotationOne, $annotationTwo));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getRequirements();

        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($annotationOne->name, $actual);
        $this->assertEquals(array('dataType' => 'string', 'requirement' => null, 'description' => null), $actual['requirement1']);

        $this->assertArrayHasKey($annotationTwo->name, $actual);
        $this->assertEquals(array('dataType' => 'integer', 'requirement' => '/^\d+$/', 'description' => 'description'), $actual['requirement2']);
    }

    public function testHandleResourceAnnotation()
    {
        $annotation = new Resource();
        $event      = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertTrue($this->container->isResource());

        $annotation        = new Resource();
        $annotation->value = false;
        $event             = $this->getExtractorEvent(array($annotation));
        $this->listener->onExtractorHandle($event);

        $this->assertFalse($this->container->isResource());
    }

    public function testHandleSectionAnnotation()
    {
        $expected = 'test section';

        $annotation        = new Section();
        $annotation->value = $expected;
        $event             = $this->getExtractorEvent(array($annotation));

        $this->listener->onExtractorHandle($event);

        $this->assertEquals($expected, $this->container->getSection());
    }

    public function testHandleStatusCodeAnnotation()
    {
        $expected = array(
            200 => array('OK'),
            404 => array('Not Found', 'custom description'),
        );

        $annotationOne       = new StatusCode();
        $annotationOne->code = 200;

        $annotationTwo       = new StatusCode();
        $annotationTwo->code = 404;

        $annotationThree              = new StatusCode();
        $annotationThree->code        = 404;
        $annotationThree->description = 'custom description';

        $event = $this->getExtractorEvent(array($annotationOne, $annotationTwo, $annotationThree));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getStatusCodes();

        $this->assertCount(2, $actual);

        $this->assertEquals($expected, $actual);
    }

    public function testHandleTagAnnotation()
    {
        $annotationOne        = new Tag();
        $annotationOne->value = 'tag1';

        $annotationTwo        = new Tag();
        $annotationTwo->value = 'tag2';
        $event                = $this->getExtractorEvent(array($annotationOne, $annotationTwo));

        $this->listener->onExtractorHandle($event);

        $actual = $this->container->getTags();

        $this->assertCount(2, $actual);
        $this->assertContains($annotationOne->value, $actual);
        $this->assertContains($annotationTwo->value, $actual);
    }
}
 