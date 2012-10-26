<?php

namespace ServerGrove\KbBundle\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Document\Url;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadArticlesData
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LoadArticlesData implements FixtureInterface, OrderedFixtureInterface
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
        $description = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'article-description.md');

        $article = $this->addArticle(
            $manager,
            'The title of my article',
            $description,
            array($manager->find(null, '/categories/test')),
            array('My keyword', 'homepage'),
            $manager->find(null, '/url/announcing-multi-lingual-support-for-control-panel'),
            array(array('key'   => 'test-key', 'value' => 'test-value'))
        );

        $article->setTitle('El título de mi artículo');
        $article->setContent('El contenido de mi artículo');
        $manager->bindTranslation($article, 'es');

        $article = $this->addArticle(
            $manager,
            'The title of the other article',
            $description,
            array_map(
                function ($category) use ($manager) {
                    return $manager->find(null, '/categories/'.$category);
                },
                array('test/child', 'homepage', 'test', 'category-a', 'category-c')
            ),
            array('My super keyword', 'feature'),
            $manager->find(null, '/url/control-panel-v2-launched-with-mongohosting-and-lots-more'),
            array(array('key' => 'test-key-2', 'value' => 'test-value-2'))
        );

        $manager->flush();

        /** @var $user \ServerGrove\KbBundle\Document\User */
        $user = $manager->find(null, '/users/editor');
        $user->subscribe($article);

        $manager->persist($user);

        $manager->flush();
    }

    private function addArticle(
        $manager,
        $title,
        $content,
        array $categories,
        array $keywords,
        $url = null,
        array $metadata = array()
    ) {
        $article = new \ServerGrove\KbBundle\Document\Article();
        $article
            ->setTitle($title)
            ->setContent($content)->setContentType('markdown')
            ->setIsActive(true);

        if (!is_null($url)) {
            $article->addUrl($url);
        }

        foreach ($categories as $category) {
                $article->addCategory($category);
        }

        foreach ($keywords as $keyword) {
            $article->addKeyword($keyword);
        }

        foreach ($metadata as $meta) {
            $article->setMetadata($meta['key'], $meta['value']);
        }

        $manager->persist($article);
        $manager->bindTranslation($article, 'en');

        return $article;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}
