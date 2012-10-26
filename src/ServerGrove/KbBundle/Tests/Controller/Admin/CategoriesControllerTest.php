<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;

/**
 * Class CategoriesControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoriesControllerTest extends ControllerTestCase
{
    public function getPaths()
    {
        return array(
            array(null),
            array('test'),
        );
    }

    /**
     * @param string $path
     *
     * @dataProvider getPaths
     */
    public function testIndexAction($path)
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl(is_null($path) ? 'sgkb_admin_categories_index' : 'sgkb_admin_categories_show', array('path' => $path)));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
        $this->assertGreaterThan(0, $crawler->filter('table.table tbody tr')->count());

        if (is_null($path)) {
            $this->assertEquals(0, $crawler->filter('h1:contains("Category")')->count());
            $this->assertEquals('Categories list', $crawler->filter('#doccontent h2')->first()->text());
        } else {
            $this->assertEquals('Subcategories list', $crawler->filter('#doccontent h2')->first()->text());
        }
    }

    /**
     * @param string $path
     *
     * @dataProvider getPaths
     */
    public function testNewAction($path)
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl(is_null($path) ? 'sgkb_admin_categories_new' : 'sgkb_admin_categories_new_subcategory', array('path' => $path)));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
        $this->assertEquals('Category creation', $crawler->filter('form legend')->first()->text());
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Create')->form();

        $form['category[name]']        = $name = 'Test category';
        $form['category[description]'] = 'This is the description of the Test category';

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $category = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:Category')->findOneBy(array('path' => ltrim($path.'/test-category', '/')));
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Category', $category);

        $this->assertEquals($name, $category->getName());

        if (!is_null($path)) {
            $this->assertInstanceOf('ServerGrove\KbBundle\Document\Category', $category->getParent());
            $this->assertEquals($path, $category->getParent()->getPath());
        }
    }

    /**
     * @depends testNewAction
     */
    public function testCreateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_categories_create'));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    /**
     * @param string $path
     *
     * @dataProvider getPaths
     */
    public function testEditAction($path)
    {
        if (is_null($path)) {
            return;
        }

        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_categories_edit', array('path' => $path)));

        $this->assertRegExp('/^Category translation for locale \"[a-z_]{2,7}\"$/', $crawler->filter('form legend')->eq(1)->text());
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Save')->form();
        $name = 'New name '.md5(microtime(true));

        $form['category_translation_en[name]'] = $name;

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $category = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:Category')->findOneBy(array('path' => $path));
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Category', $category);
        $this->getDocumentManager()->refresh($category);

        $this->assertEquals($name, $category->getName());
    }

    /**
     * @depends testEditAction
     */
    public function testUpdateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_categories_update', array('path'   => 'test',
                                                                                          'locale' => 'en')));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    public function testDeleteAction()
    {
        $path = 'test';

        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_categories_edit', array('path' => $path)));

        $this->markTestIncomplete();
        $this->assertGreaterThan(0, $crawler->filter('button#btn_sgkb_admin_categories_delete')->count());

        $form = $crawler->selectButton('btn_sgkb_admin_categories_delete')->form();

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $category = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:Category')->findOneBy(array('path' => $path));
        $this->assertNotInstanceOf('ServerGrove\KbBundle\Document\Category', $category);
    }
}
