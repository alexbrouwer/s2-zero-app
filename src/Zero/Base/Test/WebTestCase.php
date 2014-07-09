<?php


namespace Zero\Base\Test;

use Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader as DoctrineFixturesLoader;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader as SymfonyFixturesLoader;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends BaseWebTestCase
{

    protected $environment = 'test';

    protected $containers;

    protected $kernelDir;

    // 5 * 1024 * 1024 KB
    protected $maxMemory = 5242880;

    /**
     * @var array
     */
    static private $cachedMetadatas = array();

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

    protected function assertHeaderExists(Response $response, $header)
    {
        $this->assertTrue($response->headers->has($header), sprintf('Response does not contain header "%s"', $header));
    }

    protected function assertHeaderNotExists(Response $response, $header)
    {
        $this->assertFalse($response->headers->has($header), sprintf('Response contains header "%s"', $header));
    }

    protected function assertHeaderEquals(Response $response, $headerKey, $headerValue)
    {
        $this->assertTrue(
            $response->headers->contains($headerKey, $headerValue),
            sprintf('Response does not contain header "%s" with "%s"', $headerKey, $headerValue)
        );
    }

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

    /**
     * This function finds the time when the data blocks of a class definition
     * file were being written to, that is, the time when the content of the
     * file was changed.
     *
     * @param string $class The fully qualified class name of the fixture class to
     * check modification date on.
     *
     * @return \DateTime|null
     */
    protected function getFixtureLastModified($class)
    {
        $lastModifiedDateTime = null;

        $reflClass     = new \ReflectionClass($class);
        $classFileName = $reflClass->getFileName();

        if (file_exists($classFileName)) {
            $lastModifiedDateTime = new \DateTime();
            $lastModifiedDateTime->setTimestamp(filemtime($classFileName));
        }

        return $lastModifiedDateTime;
    }

    /**
     * Determine if the Fixtures that define a database backup have been
     * modified since the backup was made.
     *
     * @param array $classNames The fixture classnames to check
     * @param string $backup The fixture backup SQLite database file path
     *
     * @return bool TRUE if the backup was made since the modifications to the
     * fixtures; FALSE otherwise
     */
    protected function isBackupUpToDate(array $classNames, $backup)
    {
        $backupLastModified = new \DateTime();
        $backupLastModified->setTimestamp(filemtime($backup));

        foreach ($classNames as &$className) {
            $fixtureLastModified = $this->getFixtureLastModified($className);
            if ($backupLastModified < $fixtureLastModified) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set the database to the provided fixtures.
     *
     * Drops the current database and then loads fixtures using the specified
     * classes. The parameter is a list of fully qualified class names of
     * classes that implement Doctrine\Common\DataFixtures\FixtureInterface
     * so that they can be loaded by the DataFixtures Loader::addFixture
     *
     * When using SQLite this method will automatically make a copy of the
     * loaded schema and fixtures which will be restored automatically in
     * case the same fixture classes are to be loaded again. Caveat: changes
     * to references and/or identities may go undetected.
     *
     * Depends on the doctrine data-fixtures library being available in the
     * class path.
     *
     * @param array $classNames List of fully qualified class names of fixtures to load
     * @param string $omName The name of object manager to use
     * @param string $registryName The service id of manager registry to use
     * @param int $purgeMode Sets the ORM purge mode
     *
     * @return null|\Doctrine\Common\DataFixtures\Executor\AbstractExecutor
     */
    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $container = $this->getContainer();
        $registry  = $container->get($registryName);
        if ($registry instanceof ManagerRegistry) {
            $om   = $registry->getManager($omName);
            $type = $registry->getName();
        } else {
            $om   = $registry->getEntityManager($omName);
            $type = 'ORM';
        }

        $executorClass       = 'PHPCR' === $type && class_exists('Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor')
            ? 'Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor'
            : 'Doctrine\\Common\\DataFixtures\\Executor\\' . $type . 'Executor';
        $referenceRepository = new ProxyReferenceRepository($om);
        $cacheDriver         = $om->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        if ('ORM' === $type) {
            $connection = $om->getConnection();
            if ($connection->getDriver() instanceof SqliteDriver) {
                $params = $connection->getParams();
                if (isset($params['master'])) {
                    $params = $params['master'];
                }

                $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
                if (!$name) {
                    $msg = "Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.";
                    throw new \InvalidArgumentException($msg);
                }

                if (!isset(self::$cachedMetadatas[$omName])) {
                    self::$cachedMetadatas[$omName] = $om->getMetadataFactory()->getAllMetadata();
                }
                $metadatas = self::$cachedMetadatas[$omName];

                $backup = $container->getParameter('kernel.cache_dir') . '/test_' . md5(serialize($metadatas) . serialize($classNames)) . '.db';
                if (file_exists($backup) && file_exists($backup . '.ser') && $this->isBackupUpToDate($classNames, $backup)) {
                    $om->flush();
                    $om->clear();

                    $executor = new $executorClass($om);
                    $executor->setReferenceRepository($referenceRepository);
                    $executor->getReferenceRepository()->load($backup);

                    copy($backup, $name);

                    $this->postFixtureRestore();

                    return $executor;
                }

                // TODO: handle case when using persistent connections. Fail loudly?
                $schemaTool = new SchemaTool($om);
                $schemaTool->dropDatabase($name);
                if (!empty($metadatas)) {
                    $schemaTool->createSchema($metadatas);
                }
                $this->postFixtureSetup();

                $executor = new $executorClass($om);
                $executor->setReferenceRepository($referenceRepository);
            }
        }

        if (empty($executor)) {
            $purgerClass = 'Doctrine\\Common\\DataFixtures\\Purger\\' . $type . 'Purger';
            if ('PHPCR' === $type) {
                $purger      = new $purgerClass($om);
                $initManager = $container->has('doctrine_phpcr.initializer_manager')
                    ? $container->get('doctrine_phpcr.initializer_manager')
                    : null;

                $executor = new $executorClass($om, $purger, $initManager);
            } else {
                $purger = new $purgerClass();
                if (null !== $purgeMode) {
                    $purger->setPurgeMode($purgeMode);
                }

                $executor = new $executorClass($om, $purger);
            }

            $executor->setReferenceRepository($referenceRepository);
            $executor->purge();
        }

        $loader = $this->getFixtureLoader($container, $classNames);

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);
        }

        return $executor;
    }

    /**
     * Callback function to be executed after Schema creation.
     * Use this to execute acl:init or other things necessary.
     */
    protected function postFixtureSetup()
    {
    }

    /**
     * Callback function to be executed after Schema restore.
     */
    protected function postFixtureRestore()
    {
    }

    /**
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param ContainerInterface $container
     * @param array $classNames
     *
     * @return Loader
     */
    protected function getFixtureLoader(ContainerInterface $container, array $classNames)
    {
        $loader = class_exists('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader')
            ? new DataFixturesLoader($container)
            : (class_exists('Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader')
                ? new DoctrineFixturesLoader($container)
                : new SymfonyFixturesLoader($container));

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        return $loader;
    }

    /**
     * Load a data fixture class.
     *
     * @param Loader $loader
     * @param string $className
     */
    protected function loadFixtureClass($loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);

            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }

    /**
     * Get an instance of the dependency injection container.
     * (this creates a kernel *without* parameters).
     *
     * @return object
     */
    protected function getContainer()
    {
        if (!empty($this->kernelDir)) {
            $tmpKernelDir          = isset($_SERVER['KERNEL_DIR']) ? $_SERVER['KERNEL_DIR'] : null;
            $_SERVER['KERNEL_DIR'] = getcwd() . $this->kernelDir;
        }

        $cacheKey = $this->kernelDir . '|' . $this->environment;
        if (empty($this->containers[$cacheKey])) {
            $options = array(
                'environment' => $this->environment
            );
            $kernel  = $this->createKernel($options);
            $kernel->boot();

            $this->containers[$cacheKey] = $kernel->getContainer();
        }

        if (isset($tmpKernelDir)) {
            $_SERVER['KERNEL_DIR'] = $tmpKernelDir;
        }

        return $this->containers[$cacheKey];
    }
}