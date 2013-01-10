<?php

namespace ServerGrove\KbBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Util\Sluggable;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="sgkb_index")
     * @Route("/{_locale}", name="sgkb_index_locale", requirements={"_locale"="en|es|pt"})
     * @return array
     */
    public function indexAction()
    {
        $categoryName = $this->get('service_container')->getParameter('server_grove_kb.article.front_page_category');
        /** @var $category \ServerGrove\KbBundle\Document\Category */
        $category = $this->getCategoryRepository()->find(sprintf('/categories/%s', Sluggable::urlize($categoryName)));

        if ($category && $category->getArticles()->count()) {
            $keyword = $this->get('service_container')->getParameter('server_grove_kb.article.front_page_keyword');

            $article = $category->getArticles()->filter(
                function (Article $article) use ($keyword) {
                    return in_array($keyword, $article->getKeywords()->toArray());
                }
            )->first();

            if (!$article) {
                $article = $category->getArticles()->first();
            }

            return $this->forward(
                'ServerGroveKbBundle:Articles:view',
                array(
                    'article'      => $article,
                    'category'     => $article->getDefaultCategory(),
                    'registerView' => false,
                    'showComments' => false
                )
            );
        }

        return $this->forward('ServerGroveKbBundle:Categories:index');
    }

    /**
     * @Template("ServerGroveKbBundle:Default:searchForm.html.twig")
     * @return array
     */
    public function searchFormAction()
    {
        $form = $this->getSearchForm();

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/search", name="sgkb_search", requirements={"_locale"="en|es|pt"})
     * @Template
     *
     * @return array
     */
    public function searchAction()
    {
        $form = $this->getSearchForm();

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->getRequest();

        $results = array();
        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $data     = $form->getData();
                $category = null;

                if (!empty($data['category'])) {
                    $category = $this->getCategoryRepository()->find($data['category']);
                }

                $results = $this->getArticleRepository()->search($data['keywords'], $category);
            }
        }

        return array(
            'form'    => $form->createView(),
            'results' => $results
        );

    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function getSearchForm()
    {
        return $this->createForm(new \ServerGrove\KbBundle\Form\SearchArticleType($this->getCategoryRepository()));
    }
}
