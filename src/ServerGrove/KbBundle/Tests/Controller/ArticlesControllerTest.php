<?php

namespace ServerGrove\KbBundle\Tests\Controller;

/**
 * Class ArticlesControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticlesControllerTest extends ControllerTestCase
{

    public function testArticleView()
    {
        $crawler = $this->validateArticleAndGetCrawler();

        $this->assertGreaterThan(0, $crawler->filter('h2.article-title')->count());
        $this->assertEquals('The title of my article', $crawler->filter('h2.article-title')->text());
        $this->assertGreaterThan(0, $crawler->filter('.article-content h1')->count());
        $this->assertEquals('Header 1', $crawler->filter('.article-content h1')->text());

        if ($this->getClient()->getContainer()->getParameter('server_grove_kb.article.enable_related_urls')) {
            $this->assertGreaterThan(0, $crawler->filter('ul.related-urls li')->count());
        }
    }

    /**
     * @param string $locale
     * @param string $title
     *
     * @dataProvider getTestData
     */
    public function testArticleViewWithLocale($locale, $title)
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', '/'.$locale.'/categories/test/articles/the-title-of-my-article.html');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h2.article-title')->count());
        $this->assertEquals($title, $crawler->filter('h2.article-title')->first()->text());
    }

    public function getTestData()
    {
        return array(
            array('en', 'The title of my article'),
            array('es', 'El título de mi artículo'),
            array('pt', 'The title of my article(Needs to be translated by Google)'), // @TODO
        );
    }

    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function validateArticleAndGetCrawler()
    {
        /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $this->getClient()->request('GET', '/');

        if (0 == $crawler->filter($selector = '.left-nav ul li a:contains("Test")')->count()) {
            $this->markTestSkipped('Missing category for article');
        }

        $link    = $crawler->filter($selector)->first()->link();
        $crawler = $this->getClient()->click($link);

        if (0 == $crawler->filter($selector = '.articles a:contains("The title of my article")')->count()) {
            $this->markTestSkipped('Missing article');
        }

        $link    = $crawler->filter($selector)->first()->link();
        $crawler = $this->getClient()->click($link);

        return $crawler;
    }
}
