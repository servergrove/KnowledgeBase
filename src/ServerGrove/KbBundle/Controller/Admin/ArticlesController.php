<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Document\Category;
use ServerGrove\KbBundle\Form\ArticleType;
use ServerGrove\KbBundle\Form\ArticleNewType;
use ServerGrove\KbBundle\Form\ArticleTranslationType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Article controller.
 *
 * @Route("/admin/{_locale}/articles", requirements={"_locale"="en|es|pt"})
 */
class ArticlesController extends Controller
{

    /**
     * Lists all Article documents.
     *
     * @Route("/", name="sgkb_admin_articles_index")
     * @Template()
     */
    public function indexAction()
    {
        $documents = $this->getArticleRepository()->findAll();

        return array('documents' => $documents);
    }

    /**
     * Finds and displays a Article document.
     *
     * @Route("/{slug}/show", name="sgkb_admin_articles_show")
     * @ParamConverter("article", class="ServerGroveKbBundle:Article")
     * @Template()
     */
    public function showAction(Article $article)
    {
        $deleteForm = $this->createDeleteForm($article);

        return array(
            'article'     => $article,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Article document.
     *
     * @Route("/new", name="sgkb_admin_articles_new")
     * @Template()
     */
    public function newAction(Category $category = null)
    {
        $document = new Article();
        if (!is_null($category)) {
            $document->addCategory($category);
        }

        $form = $this->createForm(
            new ArticleNewType(),
            $document,
            array('enable_related_urls' => $this->areRelatedUrlsEnabled())
        );

        return array(
            'document' => $document,
            'form'     => $form->createView()
        );
    }

    /**
     * Creates a new Article document.
     *
     * @Route("/create", name="sgkb_admin_articles_create")
     * @Method("post")
     */
    public function createAction()
    {
        $document = new Article();
        $request  = $this->getRequest();
        $form     = $this->createForm(
            new ArticleNewType(),
            $document,
            array('enable_related_urls' => $this->areRelatedUrlsEnabled())
        );
        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();

            $dm->persist($document);
            $dm->bindTranslation($document, $this->getDefaultLocale());
            $dm->flush();

            return $this->redirect(
                $this->generateUrl(
                    'sgkb_admin_articles_edit',
                    array(
                        'slug' => $document->getSlug()
                    )
                )
            );
        }

        return $this->render(
            'ServerGroveKbBundle:Admin/Articles:new.html.twig',
            array(
                'document' => $document,
                'form'     => $form->createView()
            ),
            new Response('', 400)
        );
    }

    /**
     * Displays a form to edit an existing Article document.
     *
     * @Route("/{slug}/edit", name="sgkb_admin_articles_edit")
     * @Template()
     * @ParamConverter("article", class="ServerGroveKbBundle:Article")
     */
    public function editAction(Article $article, array $forms = array())
    {
        $original = clone $article;
        $dm       = $this->getDocumentManager();
        $editForm = $this->createForm(
            new ArticleType(),
            $original,
            array(
                'enable_related_urls' => $this->areRelatedUrlsEnabled(),
                'loader'              => new \ServerGrove\KbBundle\Form\ChoiceList\CategoriesLoader($dm)
            )
        );

        $locales = $this->get('service_container')->getParameter('server_grove_kb.locales');

        $translationForms = array();

        foreach ($locales as $locale) {
            try {
                $tmp = $dm->findTranslation(null, $article->getId(), $locale, false);
            } catch (\RuntimeException $e) {
                $tmp = clone $original;
                $tmp->setIsActive(false)->setContent('');
            }

            $form = isset($forms[$locale]) ? $forms[$locale] : $this->createForm(
                new ArticleTranslationType($locale),
                $tmp,
                array('id_prefix' => $locale.'_')
            );

            $translationForms[$locale] = $form->createView();
        }

        return array(
            'document'          => $original,
            'edit_form'         => $editForm->createView(),
            'delete_form'       => $this->createDeleteForm($article)->createView(),
            'translation_forms' => $translationForms
        );
    }

    /**
     * Edits an existing Article document.
     *
     * @Route("/{slug}/update", name="sgkb_admin_articles_update")
     * @Method("post")
     * @ParamConverter("article", class="ServerGroveKbBundle:Article")
     */
    public function updateAction(Article $article)
    {
        $editForm = $this->createForm(
            new ArticleType(),
            $article,
            array('enable_related_urls' => $this->areRelatedUrlsEnabled())
        );

        $dm      = $this->getDocumentManager();
        $locales = $this->get('service_container')->getParameter('server_grove_kb.locales');
        $request = $this->getRequest();

        $editForm->bind($request);

        if ($valid = $editForm->isValid()) {
            $dm->checkpoint($article);
            $dm->persist($article);
        }

        $translationForms = array();

        foreach ($locales as $locale) {
            $translation = $dm->findTranslation('ServerGrove\KbBundle\Document\Article', $article->getId(), $locale);

            $form = $this->createForm(
                new ArticleTranslationType($locale),
                $translation,
                array('id_prefix' => $locale.'_')
            );

            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $dm->bindTranslation($translation, $locale);
            } else {
                $valid = false;
            }

            $translationForms[$locale] = $form;
        }

        if ($valid) {
            $dm->flush();

            $url = $request->request->get('back_to_list', false) ?
                $this->generateUrl(
                    'sgkb_admin_categories_articles',
                    array('path' => $article->getDefaultCategory()->getPath())
                ) :
                $this->generateUrl('sgkb_admin_articles_edit', array('slug' => $article->getSlug()));

            return $this->redirect($url);
        }

        return $this->render(
            'ServerGroveKbBundle:Admin/Articles:edit.html.twig',
            $this->editAction($dm->refresh($article), $translationForms),
            new Response('', 400)
        );
    }

    /**
     * Deletes a Article document.
     *
     * @Route("/{slug}/delete", name="sgkb_admin_articles_delete")
     * @Method("post")
     * @ParamConverter("article", class="ServerGroveKbBundle:Article")
     */
    public function deleteAction(Article $article)
    {
        $form    = $this->createDeleteForm($article);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();
            $dm->remove($article);
            $dm->flush();
        }

        return $this->redirect($this->generateUrl('sgkb_admin_articles_index'));
    }

    /**
     * @Route("/{slug}/keywords/sync.{_format}", name="sgkb_admin_articles_keywords_sync", requirements={"_format"="json"})
     * @Method("post")
     *
     * @param \ServerGrove\KbBundle\Document\Article $article
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function syncKeywords(Article $article)
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->createNotFoundException();
        }

        $keywords = array();

        if ($request->request->has('keywords') && is_array($tmp = $request->request->get('keywords'))) {
            $keywords = $tmp;
        }

        $article->setKeywords($keywords);

        $dm = $this->getDocumentManager();
        $dm->persist($article);
        $dm->flush($article);

        return new Response(json_encode(array('result' => true)));
    }

    /**
     * @Route("/check-article.{_format}", name="sgkb_admin_articles_check", requirements={"_format"="json"})
     * @Method("post")
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkArticleAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        if (!$request->request->has('title')) {
            throw new HttpException(400, 'Missing title');
        }

        /** @var $translator \Symfony\Bundle\FrameworkBundle\Translation\Translator */
        $translator = $this->get('translator');

        $message = $translator->trans('The article does not exists');
        $result  = true;

        $article = $this->getArticleRepository()->findOneBySlug(
            \ServerGrove\KbBundle\Util\Sluggable::urlize($request->request->get('title'))
        );
        if ($article) {
            $message = $translator->trans('The article already exists');
            $result  = false;
        }

        return new Response(json_encode(
            array(
                'result'  => $result,
                'message' => $message
            )
        ), 200, array('Content-type' => 'application/json'));
    }

    private function createDeleteForm(Article $article)
    {
        return $this->createFormBuilder(array('slug' => $article->getSlug()))
            ->add('slug', 'hidden')
            ->getForm();
    }

    /**
     * @return boolean
     */
    private function areRelatedUrlsEnabled()
    {
        return $this->get('service_container')->getParameter('server_grove_kb.article.enable_related_urls');
    }
}
