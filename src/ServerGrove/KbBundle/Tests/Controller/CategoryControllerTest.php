<?php

namespace ServerGrove\KbBundle\Tests\Controller;

class CategoryControllerTest extends ControllerTestCase
{

    public function testCategories()
    {
        /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $this->getClient()->request('GET', '/');
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Knowledge Base")')->count(), 'There is no header for KB');
        $this->assertGreaterThan(0, $crawler->filter('ul.nav li a:contains("Test")')->count());

        $link = $crawler->filter('ul.nav li a:contains("Test")')->first()->link();
        $crawler = $this->getClient()->click($link);
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Knowledge Base")')->count(), 'There is no header for KB');
        $this->assertGreaterThan(0, $crawler->filter('.breadcrumb a:contains("Test")')->count(), 'There is no breadcrumb for Test category');
        $this->assertEquals(0, $crawler->filter('.breadcrumb a:contains("Child")')->count(), 'There are some breadcrumbs for Child category');

        $this->assertGreaterThan(0, $crawler->filter('.subcategories a:contains("Child")')->count());
        $this->assertGreaterThan(0, $crawler->filter('.articles a:contains("The title of my article")')->count());

        $link = $crawler->filter('.subcategories a:contains("Child")')->first()->link();
        $crawler = $this->getClient()->click($link);
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Knowledge Base")')->count(), 'There is no header for KB');
        $this->assertGreaterThan(0, $crawler->filter('.breadcrumb a:contains("Test")')->count(), 'There is no breadcrumb for Test category');
        $this->assertGreaterThan(0, $crawler->filter('.breadcrumb a:contains("Child")')->count(), 'There is no breadcrumb for Child category');
        $this->assertEquals(0, $crawler->filter('.subcategories a')->count());
        $this->assertGreaterThan(0, $crawler->filter('.articles li')->count());
    }
}
