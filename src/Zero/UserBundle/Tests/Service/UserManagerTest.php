<?php


namespace Zero\UserBundle\Tests\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Zero\Base\Test\DoctrineTestHelper;
use Zero\Base\Test\KernelTestHelper;
use Zero\UserBundle\Service\UserManager;
use Zero\UserBundle\Tests\Fixtures\Doctrine\UserGroupData;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_CLASS = 'Zero\UserBundle\Entity\User';
    const FORM_CLASS = 'Zero\UserBundle\Form\Type\UserType';

    /**
     * @var \AppKernel
     */
    private $kernel;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->kernel = KernelTestHelper::createTestKernel();
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();

        $this->formFactory   = $this->container->get('form.factory');
        $this->objectManager = $this->container->get('doctrine.orm.entity_manager');

        DoctrineTestHelper::createSchema($this->objectManager);
        DoctrineTestHelper::loadTestDataFixtures($this->objectManager, $this->getDataFixtures());

        parent::setUp();
    }

    private function getDataFixtures()
    {
        return array(
            new UserGroupData()
        );
    }

    /**
     * @return null
     */
    public function tearDown()
    {
        $this->kernel->shutdown();

        DoctrineTestHelper::dropSchema($this->objectManager);

        parent::tearDown();
    }

    public function testCreate()
    {
        $expected = array(
            'user' => array(
                'username'    => 'testuser',
                'email'       => 'test@example.com',
                'displayName' => 'test user',
                'groups'      => array(1)
            )
        );

        // Setup manager
        $manager = new UserManager(self::ENTITY_CLASS, self::FORM_CLASS);
        $manager->setFormFactory($this->formFactory);

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('persist');
        $objectManager->shouldReceive('flush');
        $manager->setObjectManager($objectManager);

        // Add CSRF token
        $expected['user']['_token'] = $this->container->get('form.csrf_provider')->generateCsrfToken('user');

        $manager->create($expected);
    }
}
 