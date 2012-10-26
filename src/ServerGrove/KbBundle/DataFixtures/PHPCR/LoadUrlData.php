<?php

namespace ServerGrove\KbBundle\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use ServerGrove\KbBundle\Document\Url;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadUrlData
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LoadUrlData implements FixtureInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $this->addUrl($manager, 'Control Panel V2 launched with MongoHosting and lots more', 'http://blog.servergrove.com/2012/05/14/control-panel-v2-launched-with-mongohosting-and-lots-more/');
        $url = $this->addUrl($manager, 'Announcing multi-lingual support for Control Panel', 'http://blog.servergrove.com/2012/01/17/announcing-multi-lingual-support-for-control-panel/');

        $url->setName('Nuestro Panel de Control habla español!')->setUrl('http://blog.servergrove.com/2012/01/17/nuestro-panel-de-control-habla-espanol/');
        $manager->bindTranslation($url, 'es');

        $manager->flush();
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param string                                     $name
     * @param string                                     $url
     *
     * @return \ServerGrove\KbBundle\Document\Url
     */
    private function addUrl(ObjectManager $manager, $name, $url)
    {
        $document = new Url();
        $document->setName($name);
        $document->setUrl($url);
        $manager->persist($document);

        $manager->bindTranslation($document, 'en');

        return $document;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}
