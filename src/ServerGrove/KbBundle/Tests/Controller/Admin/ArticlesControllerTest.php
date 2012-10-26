<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;

use ServerGrove\KbBundle\Util\Sluggable;

/**
 * Class ArticlesControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticlesControllerTest extends ControllerTestCase
{
    private $title = 'The title of my article';

    public function testIndexAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_index'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $this->assertGreaterThan(0, $crawler->filter('h1')->count());
        $this->assertEquals('Article list', $crawler->filter('h1')->eq(0)->text());
        $this->assertGreaterThan(0, $crawler->filter('table.table tbody tr')->count());
    }

    public function testShowAction()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_show', array('slug' => Sluggable::urlize($this->title))));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $this->assertGreaterThan(0, $crawler->filter('h1')->count());
        $this->assertEquals('Article "'.$this->title.'"', $crawler->filter('h1')->eq(0)->text());
    }

    public function testNewAction()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_new'));
        $form    = $crawler->selectButton('Create')->form();

        $form['article[title]'] = 'My article name';
        $form['article[categories]']->select('/categories/test');

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        /** @var $article \ServerGrove\KbBundle\Document\Article */
        $article = $this->getArticleRepository()->find('/articles/my-article-name');
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Article', $article);

        $this->assertCount(1, $article->getCategories());

        /** @var $category \ServerGrove\KbBundle\Document\Category */
        $category = $article->getCategories()->first();
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Category', $category);

        $this->assertEquals('Test', $category->getName());
    }

    /**
     * @depends testNewAction
     */
    public function testCreateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_articles_create'));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    /**
     * @depends testNewAction
     */
    public function testEditAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_edit', array('slug' => $slug = Sluggable::urlize($this->title))));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $form = $crawler->selectButton('Save')->form();
        $form['article[categories]']->select(array('/categories/mysql', '/categories/billing'));

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        /** @var $article \ServerGrove\KbBundle\Document\Article */
        $article = $this->getDocumentManager()->refresh($this->getArticleRepository()->find('/articles/'.$slug));
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\Article', $article);

        $this->assertCount(2, $article->getCategories());

        /** @var $category \ServerGrove\KbBundle\Document\Category */
        foreach ($article->getCategories() as $category) {
            $this->assertInstanceOf('ServerGrove\KbBundle\Document\Category', $category);
            $this->assertTrue(in_array($category->getName(), array('MySQL', 'Billing')));
        }

        $crawler = $client->request('GET', $url = $this->generateUrl('sgkb_admin_articles_edit', array('slug' => $slug = Sluggable::urlize($this->title))));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $form = $crawler->selectButton('Save')->form();

        $form['translation_es[title]'] = 'Título en español';
        $form['translation_es[isActive]']->tick();
        $form['translation_es[content]'] = 'Contenido en español';
        $form['back_to_list']->untick();

        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $this->assertEquals('Redirecting to '.$url, $crawler->filter('title')->first()->text());
    }

    /**
     * @depends testEditAction
     */
    public function testUpdateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_articles_update', array('slug' => Sluggable::urlize($this->title))));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    /**
     * @depends testNewAction
     */
    public function testDeleteAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_edit', array('slug' => $slug = Sluggable::urlize($this->title))));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $this->markTestIncomplete();
        $this->assertGreaterThan(0, $crawler->filter('button#btn_sgkb_admin_articles_delete')->count());

        $form = $crawler->selectButton('btn_sgkb_admin_articles_delete')->form();

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        /** @var $article \ServerGrove\KbBundle\Document\Article */
        $article = $this->getArticleRepository()->find('/articles/'.$slug);
        $this->assertNotInstanceOf('ServerGrove\KbBundle\Document\Article', $article);
    }
}
