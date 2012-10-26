<?php

namespace ServerGrove\KbBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class WebTestCase
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class WebTestCase extends BaseTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var Application
     */
    private $application;

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->setUpSchemas();
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     * @throws \RuntimeException
     */
    protected function getContainer()
    {
        if (!static::$kernel) {
            throw new \RuntimeException('There is no Kernel instance');
        }

        return static::$kernel->getContainer();
    }

    /**
     * @return \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->get('doctrine_phpcr.odm.document_manager');
    }

    /**
     * Loads all the necessary data for tests
     */
    private function setupSchemas()
    {
        $path = __DIR__.'/..';
        $this
            ->getApplication()
            ->find('doctrine:phpcr:fixtures:load')
            ->run($this->getInputDefinition(array('--fixtures' => $path.'/DataFixtures/PHPCR')), new NullOutput());
    }

    /**
     * @param array $def
     *
     * @return \Symfony\Component\Console\Input\ArrayInput
     */
    private function getInputDefinition(array $def = array())
    {
        return new ArrayInput(array_merge(array('--env' => 'test', '-v'), $def));
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    private function getApplication()
    {
        if (!$this->application) {
            $this->application = new Application(static::$kernel);

            foreach (static::$kernel->getBundles() as $bundle) {
                $bundle->registerCommands($this->application);
            }
        }

        return $this->application;
    }
}
