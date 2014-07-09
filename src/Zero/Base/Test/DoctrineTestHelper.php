<?php


namespace Zero\Base\Test;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineTestHelper
{
    /**
     * Returns an entity manager for testing.
     *
     * @param ContainerInterface $container
     *
     * @return EntityManager
     */
    public static function createTestEntityManager(ContainerInterface $container)
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            \PHPUnit_Framework_TestCase::markTestSkipped('This test requires SQLite support in your environment');
        }

        $em = $container->get('doctrine.orm.entity_manager');
        $config = $em->getConfiguration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        return EntityManager::create($params, $config);
    }

    /**
     * @param ObjectManager $objectManager
     * @param FixtureInterface[] $fixtures
     */
    public static function loadTestDataFixtures(ObjectManager $objectManager, array $fixtures)
    {
        $purger   = new ORMPurger($objectManager);
        $executor = new ORMExecutor($objectManager, $purger);
        $executor->execute($fixtures);
    }

    /**
     * Create schema
     *
     * @param ObjectManager $objectManager
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createSchema(ObjectManager $objectManager)
    {
        $metadatas = $objectManager->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($objectManager);
        $tool->createSchema($metadatas);
    }

    /**
     * Drop schema
     *
     * @param ObjectManager $objectManager
     */
    public static function dropSchema(ObjectManager $objectManager)
    {
        $metadatas = $objectManager->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($objectManager);
        $tool->dropSchema($metadatas);
    }
} 