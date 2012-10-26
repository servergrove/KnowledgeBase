<?php

namespace ServerGrove\KbBundle\Twig;

use Doctrine\ODM\PHPCR\DocumentManager;
use ServerGrove\KbBundle\Document\Article;
use Symfony\Component\Locale\Locale;

/**
 * Class ArticleExtension
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticleExtension extends \Twig_Extension
{
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    private $manager;

    /**
     * @var string
     */
    private $template;

    /**
     * @var \Twig_Template
     */
    private $twig;

    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $manager
     * @param array                               $locales
     * @param string                              $template
     */
    public function __construct(DocumentManager $manager, array $locales, $template)
    {
        $this->manager  = $manager;
        $this->locales  = $locales;
        $this->template = $template;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        parent::initRuntime($environment);

        $this->twig = $environment->loadTemplate($this->template);
    }

    public function getTests()
    {
        return array('active' => new \Twig_Test_Method($this, 'hasLocaleActive'));
    }

    public function getFunctions()
    {
        return array(
            'article_locale'  => new \Twig_Function_Method($this, 'renderArticleLocale', array('is_safe' => array('html'))),
            'article_locales' => new \Twig_Function_Method($this, 'renderArticleLocales', array('is_safe' => array('html')))
        );
    }

    /**
     * @param  \ServerGrove\KbBundle\Document\Article $article
     * @param  string                                 $locale
     * @return string
     */
    public function renderArticleLocale(Article $article, $locale)
    {
        try {
            $active = $this
                ->manager
                ->findTranslation(get_class($article), $article->getId(), $locale, false)
                ->getIsActive();
        } catch (\RuntimeException $e) {
            $active = false;
        }

        $this->manager->refresh($article);

        return $this->twig->renderBlock('article_locale', array(
            'active'      => $active,
            'locale'      => $locale,
            'locale_name' => Locale::getDisplayLanguage($locale)
        ));
    }

    /**
     * @param  \ServerGrove\KbBundle\Document\Article $article
     * @return string
     */
    public function renderArticleLocales(Article $article)
    {
        return $this->twig->renderBlock('article_locales', array('article' => $article, 'locales'=> $this->locales));
    }

    /**
     * @param  \ServerGrove\KbBundle\Document\Article $article
     * @return mixed
     */
    public function hasLocaleActive(Article $article)
    {
        $locales = $this->getManager()->getLocalesFor($article);

        do {
            $tmp    = $this->getManager()->findTranslation(get_class($article), $article->getId(), current($locales));
            $active = $tmp->getIsActive();
        } while (!$active && next($locales));

        $this->getManager()->refresh($article);

        return $active;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sgkb_article';
    }

    /**
     * Returns the value of manager
     *
     * @return \Doctrine\ODM\PHPCR\DocumentManager
     */
    private function getManager()
    {
        return $this->manager;
    }
}
