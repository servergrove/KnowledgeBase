<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use ServerGrove\KbBundle\Document\Article;
use ServerGrove\KbBundle\Document\Category;
use ServerGrove\KbBundle\Form\CategoryType;
use ServerGrove\KbBundle\Form\CategorySettingsType;

/**
 * Class CategoriesAdminController
 *
 * @Route("/admin/{_locale}/categories", requirements={"_locale"="en|es|pt"})
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoriesController extends Controller
{

    /**
     * @Route("/", name="sgkb_admin_categories_index")
     * @Route("/{path}/show", name="sgkb_admin_categories_show", requirements={"path":".+"})
     * @Template
     *
     * @param string|null $path
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function indexAction($path = null)
    {
        if (!is_null($path)) {
            if (!$category = $this->getCategoryRepository()->findOneBy(array('path' => $path))) {
                throw $this->createNotFoundException('Unable to find Category document.');
            }

            return array('category' => $category, 'categories' => $category->getChildren());
        }

        return array('categories' => $this->getCategoryRepository()->findAllParents());
    }

    /**
     *
     * @Route("/new", name="sgkb_admin_categories_new")
     * @Route("/{path}/categories/new", name="sgkb_admin_categories_new_subcategory", requirements={"path":".+"})
     * @Template
     * @ParamConverter("parent", class="ServerGroveKbBundle:Category")
     *
     * @param \ServerGrove\KbBundle\Document\Category $parent
     *
     * @return array
     */
    public function newAction(Category $parent = null)
    {
        $document = new Category();
        $form     = $this->createForm(new CategoryType(), $document);

        if (!is_null($parent)) {
            $document->setParent($parent);
        }

        return array(
            'document' => $document,
            'parent'   => $parent,
            'form'     => $form->createView()
        );
    }

    /**
     * @Route("/create", name="sgkb_admin_categories_create")
     * @Route("/{path}/categories/create", name="sgkb_admin_categories_create_subcategory", requirements={"path":".+"})
     * @Method("post")
     * @ParamConverter("parent", class="ServerGroveKbBundle:Category")
     *
     * @param \ServerGrove\KbBundle\Document\Category $parent
     *
     * @return array
     */
    public function createAction(Category $parent = null)
    {
        $document = new Category();
        $request  = $this->getRequest();
        $form     = $this->createForm(new CategoryType(), $document);

        $form->bind($request);
        if ($form->isValid()) {
            $dm = $this->getDocumentManager();

            if (is_null($parent)) {
                /** @var $session \PHPCR\SessionInterface */
                $session = $dm->getPhpcrSession();
                $root    = $session->getNode('/');
                $root->hasNode('categories') || $root->addNode('categories');

                $document->setParent($dm->find(null, '/categories'));
            } else {
                $document->setParent($parent);
            }

            $dm->persist($document);
            $dm->bindTranslation($document, $this->getDefaultLocale());
            $dm->flush();

            return $this->redirect(
                $this->generateUrl('sgkb_admin_categories_show', array('path' => $document->getPath()))
            );
        }

        return $this->render(
            'ServerGroveKbBundle:Admin/Categories:new.html.twig',
            array(
                'document' => $document,
                'parent'   => $parent,
                'form'     => $form->createView()
            ),
            new Response('', 400)
        );
    }

    /**
     * Displays a form to edit an existing Category document.
     *
     * @Route("/{path}/edit", name="sgkb_admin_categories_edit", requirements={"path":".+"})
     * @Template()
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     *
     * @param \ServerGrove\KbBundle\Document\Category $category
     * @param array                                   $forms
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function editAction(Category $category, array $forms = array())
    {
        $original = clone $category;
        $dm       = $this->getDocumentManager();
        $locales  = $this->get('service_container')->getParameter('server_grove_kb.locales');

        $translationForms = array();

        foreach ($locales as $locale) {
            try {
                $tmp = $dm->findTranslation(get_class($category), $category->getId(), $locale, false);
            } catch (\RuntimeException $e) {
                $tmp = clone $original;
                $tmp->setLocale($locale)->setDescription('');
            }

            $form = isset($forms[$locale]) ?
                $forms[$locale] :
                $this->createForm(new CategoryType($locale), $tmp, array('id_prefix' => $locale.'_'));

            $translationForms[$locale] = $form->createView();
        }

        return array(
            'document'          => $category,
            'settings_form'     => $this->createForm(new CategorySettingsType(), $category)->createView(),
            'translation_forms' => $translationForms,
            'delete_form'       => $this->createDeleteForm($category)->createView()
        );
    }

    /**
     * Edits an existing Translation document.
     *
     * @Route("/{path}/update", name="sgkb_admin_categories_update", requirements={"path":".+"})
     * @Method("post")
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     */
    public function updateAction(Category $category)
    {
        $original         = clone $category;
        $locales          = $this->get('service_container')->getParameter('server_grove_kb.locales');
        $dm               = $this->getDocumentManager();
        $translationForms = array();

        $settingsForm = $this->createForm(new CategorySettingsType(), $category);
        $settingsForm->bind($this->getRequest());

        $valid = $settingsForm->isValid();
        if ($valid) {
            $dm->persist($category);
        }

        foreach ($locales as $locale) {
            $translation = $dm->findTranslation('ServerGrove\KbBundle\Document\Category', $original->getId(), $locale);

            $form = $this->createForm(new CategoryType($locale), $translation, array('id_prefix' => $locale.'_'));

            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $dm->bindTranslation($translation, $locale);
                $dm->flush();
            } else {
                $valid = false;
            }

            $translationForms[$locale] = $form;
        }

        if ($valid) {
            return $this->redirect(
                $this->generateUrl('sgkb_admin_categories_edit', array('path' => $category->getPath()))
            );
        }

        return $this->render(
            'ServerGroveKbBundle:Admin/Categories:edit.html.twig',
            $this->editAction($category, $translationForms),
            new Response('', 400)
        );
    }

    /**
     * Deletes a Category document.
     *
     * @Route("/{path}/delete", name="sgkb_admin_categories_delete", requirements={"path":".+"})
     * @Method("post")
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     *
     */
    public function deleteAction(Category $category)
    {
        $form    = $this->createDeleteForm($category);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();

            $this->removeChildrenFromCategory($category);
            $this->removeArticlesFromCategory($category);

            $dm->remove($category);
            $dm->flush();
        }

        return $this->redirect($this->generateUrl('sgkb_admin_categories_index'));
    }

    /**
     * Lists all Article documents.
     *
     * @Route("/{path}/articles", name="sgkb_admin_categories_articles", requirements={"path":".+"})
     * @Template("ServerGroveKbBundle:Admin/Articles:index.html.twig")
     * @ParamConverter("category", class="ServerGroveKbBundle:Category")
     */
    public function articlesAction(Category $category)
    {
        $documents = $category->getArticles();

        return array('documents' => $documents, 'category' => $category);
    }

    /**
     * @Route("/{path}/articles/new", name="sgkb_admin_categories_new_article", requirements={"path":".+"})
     * @param  \ServerGrove\KbBundle\Document\Category $category
     * @return Response
     */
    public function newArticleAction(Category $category)
    {
        return $this->forward('ServerGroveKbBundle:Admin/Articles:new', array('category' => $category));
    }

    public function removeArticlesFromCategory()
    {
        /** @var $category Category */
        $category = func_get_arg(1 == func_num_args() ? 0 : 1);

        if (!($category instanceof Category)) {
            throw new \RuntimeException('Expected instance of Category');
        }

        $dm     = $this->getDocumentManager();
        $logger = $this->get('logger');

        /** @var $article Article */
        foreach ($category->getArticles() as $article) {
            $article->removeCategory($category);
            if (1 == $article->getCategories()->count()) {
                $logger->info(sprintf('Removing article "%s"', $article->getTitle()));
                $dm->remove($article);
            }
        }
    }

    public function removeChildrenFromCategory(Category $category)
    {
        $controller = $this;

        foreach ($category->getChildren() as $child) {
            call_user_func(array($controller, 'removeArticlesFromCategory'), $child);
            call_user_func(array($controller, 'removeChildrenFromCategory'), $child);

            /** @var $category Category */
            $category->getChildren()->removeElement($child);
        }
    }

    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder(array('id' => $category->getId()))
            ->add('id', 'hidden')
            ->getForm();
    }
}
