<?php

namespace ServerGrove\KbBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Document\Category;
use Symfony\Component\Locale\Locale;

/**
 * Class ArticlesController
 *
 * @Route("/{_locale}/categories/{path}/articles", requirements={"_locale"="en|es|pt", "path"=".+"})
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticlesController extends Controller
{

    /**
     *
     * @Route("/{slug}.{_format}", name="sgkb_articles_view", defaults={"_format"="html"}, requirements={"_format"="html|json|xml"})
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     * @ParamConverter("article", class="ServerGroveKbBundle:Article")
     * @Template
     *
     * @param Article  $article
     * @param Category $category
     * @param bool     $registerView
     * @param bool     $searchForm
     * @param bool     $showComments
     *
     * @return array
     */
    public function viewAction(Article $article, Category $category, $registerView = true, $searchForm = false, $showComments = true)
    {
        if ($registerView) {
            $this->registerView($article);
        }

        $this->checkLocale($article);

        return array(
            'category'     => $category,
            'article'      => $article,
            'searchForm'   => $searchForm,
            'showComments' => $showComments
        );
    }

    private function checkLocale(Article $article)
    {
        $dm      = $this->getDocumentManager();
        $locales = $dm->getLocalesFor($article);

        $locale = $this->getRequest()->getLocale();
        if (!in_array($locale, $locales)) {
            $article->setTitle($article->getTitle().'(Needs to be translated by Google)');
        }
    }

    /**
     * @Template
     *
     * @param  \ServerGrove\KbBundle\Document\Article  $article
     * @param  \ServerGrove\KbBundle\Document\Category $category
     * @return array
     */
    public function articleContentAction(Article $article, Category $category = null, $showComments = true)
    {
        !is_null($category) || $category = $article->getDefaultCategory();
        $locales       = $this->getActiveLocales($article, $this->getDocumentManager()->getLocalesFor($article));
        $localeNames   = $this->getLocaleNames($locales);
        $currentLocale = $this->getRequest()->getLocale();

        foreach ($locales as $locale) {
            $localeNames[$locale]['current'] = $currentLocale == $locale;
            $localeNames[$locale]['path']    = $this->generateUrl(
                'sgkb_articles_view',
                array(
                    '_format' => $this->getRequest()->get('_format'),
                    '_locale' => $locale,
                    'slug'    => $article->getSlug(),
                    'path'    => $category->getPath()
                )
            );
        }

        $article = $this->getDocumentManager()->refresh($article);
        $this->checkLocale($article);

        $articles = $category->getArticles();
        $index    = $articles->indexOf($article);

        $container = $this->get('service_container');

        return array(
            'article'             => $article,
            'category'            => $category,
            'locales'             => $locales,
            'localeNames'         => $localeNames,
            'previousArticle'     => $articles->get($index - 1),
            'nextArticle'         => $articles->get($index + 1),
            'enable_related_urls' => $container->getParameter('server_grove_kb.article.enable_related_urls'),
            'showComments'        => $showComments
        );
    }

    /**
     * @param \ServerGrove\KbBundle\Document\Article $article
     */
    private function registerView(Article $article)
    {
        $article->registerView();
        $this->getDocumentManager()->persist($article);
        $this->getDocumentManager()->flush();
    }

    /**
     * @param  array $locales
     * @return array
     */
    private function getLocaleNames(array $locales)
    {
        $names = array_map(
            function ($locale) {
                return array('name'=> Locale::getDisplayLanguage($locale));
            },
            $locales
        );

        return array_combine($locales, $names);
    }

    /**
     * @param  \ServerGrove\KbBundle\Document\Article $article
     * @param  array                                  $locales
     * @return array
     */
    private function getActiveLocales(Article $article, array $locales)
    {
        $activeLocales = array();
        foreach ($locales as $locale) {
            try {
                $articleTranslation = $this->getDocumentManager()->findTranslation(
                    'ServerGrove\KbBundle\Document\Article',
                    $article->getId(),
                    $locale,
                    false
                );
                if ($articleTranslation->getIsActive()) {
                    $activeLocales[] = $locale;
                }
            } catch (\InvalidArgumentException $e) {

            }
        }

        return $activeLocales;
    }
}
