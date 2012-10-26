<?php

namespace ServerGrove\KbBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Document\Category;

/**
 * Class CategoriesController
 *
 * @Route("/{_locale}/categories", requirements={"_locale"="en|es|pt"})
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoriesController extends Controller
{

    /**
     * @Route("/", name="sgkb_categories_index")
     * @Template
     *
     * @param array $categories
     *
     * @return array
     */
    public function indexAction($categories = null)
    {
        return array();
    }

    /**
     * @Template
     *
     * @param  \ServerGrove\KbBundle\Document\Category $category
     * @param  \ServerGrove\KbBundle\Document\Category $topCategory
     * @return array
     */
    public function selectorAction(Category $category = null, Category $topCategory = null)
    {
        $context    = $this->get('security.context');
        $repository = $this->getCategoryRepository();
        if (is_null($category)) {
            $categories = $repository->findAllParentsActive($context->isGranted('ROLE_USER'));
        } else {
            $categories = $category->getChildren()->filter(
                $repository->getFilterClosure($context->isGranted('ROLE_USER'))
            );
        }

        $filteredCategories = new ArrayCollection();

        /** @var $category \ServerGrove\KbBundle\Document\Category */
        foreach ($categories as $subcategory) {
            if ($this->shouldCategoryBeDisplayed($subcategory)) {
                $filteredCategories->add($subcategory);
            }
        }

        return array('categories' => $filteredCategories, 'topCategory' => $topCategory);
    }

    /**
     * @param  \ServerGrove\KbBundle\Document\Category    $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subcategorySelectorAction(Category $category)
    {
        return $this->render(
            'ServerGroveKbBundle:Categories:subcategorySelector.html.twig',
            $this->selectorAction($category)
        );
    }

    /**
     *
     * @Route("/view/{path}.{_format}",
     *  name="sgkb_categories_view",
     *  defaults={"_format"="html"},
     *  requirements={"_format"="html|json|xml|rss","path":".+"}
     * )
     * @Template
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     *
     * @param Category $category
     *
     * @return array
     */
    public function viewAction(Category $category)
    {
        $keyword    = $this->get('service_container')->getParameter('server_grove_kb.article.top_keyword');
        $context    = $this->get('security.context');
        $repository = $this->getCategoryRepository();

        return array(
            'category'      => $category,
            'subcategories' => $category->getChildren()->filter(
                $repository->getFilterClosure($context->isGranted('ROLE_USER'))
            ),
            'articles'      => $category->getArticles(true),
            'topArticles'   => $category->getArticles(true)->filter(
                function (Article $article) use ($keyword) {
                    return in_array($keyword, $article->getKeywords()->getValues());
                }
            )
        );
    }

    /**
     * @param \ServerGrove\KbBundle\Document\Category $category
     *
     * @return bool
     */
    private function shouldCategoryBeDisplayed(Category $category)
    {
        if (0 < $category->getArticles()->count()) {
            /** @var $article \ServerGrove\KbBundle\Document\Article */
            foreach ($category->getArticles() as $article) {
                // If the category has at least one article active,
                // it should be displayed
                if ($article->getIsActive()) {
                    return true;
                }
            }
        } elseif (0 < $category->getChildren()->count()) {
            /** @var $child \ServerGrove\KbBundle\Document\Category */
            foreach ($category->getChildren() as $child) {
                if ($this->shouldCategoryBeDisplayed($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}
