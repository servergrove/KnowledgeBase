<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;

use ServerGrove\KbBundle\Util\Sluggable;

/**
 * Class UrlsControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UrlsControllerTest extends ControllerTestCase
{
    private $name = 'Announcing multi-lingual support for Control Panel';

    public function testIndexAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_urls_index'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('table.table tbody tr')->count());
        $this->assertEquals('Url list', $crawler->filter('#doccontent h1')->first()->text());
    }

    public function testNewAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_urls_new'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
        $this->assertEquals('Url creation', $crawler->filter('form legend')->first()->text());
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Create')->form();

        $form['urls[name]'] = 'ServerGrove';
        $form['urls[url]']  = 'http://www.servergrove.com';

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $url = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:Url')->find('/url/servergrove');

        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Url', $url);
        $this->assertEquals('ServerGrove', $url->getName());
    }

    /**
     * @depends testNewAction
     */
    public function testCreateAction()
    {
        $client = $this->getClient();
        $client->request('POST', $this->generateUrl('sgkb_admin_urls_create'));

        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    public function testEditAction()
    {
        $name    = $this->name;
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_urls_edit', array('slug' => $slug = Sluggable::urlize($name))));

        $this->assertEquals('Url edit for locale "en"', $crawler->filter('form legend')->first()->text());
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Save')->form();

        $form['urls_en[name]'] = $name = 'New name '.md5(microtime(true));

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $dm = $this->getDocumentManager();

        /** @var $url \ServerGrove\KbBundle\Document\Url */
        $url = $dm->getRepository('ServerGroveKbBundle:Url')->find('/url/'.$slug);
        $dm->refresh($url);

        $this->assertInstanceOf($className = 'ServerGrove\KbBundle\Document\Url', $url);
        $this->assertEquals($name, $url->getName());

        $translation = $dm->findTranslation($className, $url->getId(), 'es', false);
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Url', $translation);

        $this->setExpectedException('InvalidArgumentException');
        $dm->findTranslation($className, $url->getId(), 'pt', false);
    }

    /**
     * @depends testEditAction
     */
    public function testUpdateAction()
    {
        $client = $this->getClient();
        $client->request('POST', $this->generateUrl('sgkb_admin_urls_update', array('slug' => Sluggable::urlize($this->name))));

        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }
}
