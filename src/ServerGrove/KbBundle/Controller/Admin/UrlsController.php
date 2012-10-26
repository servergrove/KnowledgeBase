<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ServerGrove\KbBundle\Document\Url;
use ServerGrove\KbBundle\Form\UrlType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Url controller.
 *
 * @Route("/admin/{_locale}/urls")
 */
class UrlsController extends Controller
{
    /**
     * Lists all Url documents.
     *
     * @Route("/", name="sgkb_admin_urls_index")
     * @Template()
     */
    public function indexAction()
    {
        $dm = $this->getDocumentManager();

        $documents = $dm->getRepository('ServerGroveKbBundle:Url')->findAll();

        return array('documents' => $documents);
    }

    /**
     * Finds and displays a Url document.
     *
     * @Route("/{slug}/show", name="sgkb_admin_urls_show")
     * @Template()
     * @ParamConverter("url", class="ServerGroveKbBundle:Url")
     */
    public function showAction(Url $url)
    {
        $deleteForm = $this->createDeleteForm($url);

        return array(
            'document'      => $url,
            'delete_form'   => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Url document.
     *
     * @Route("/new", name="sgkb_admin_urls_new")
     * @Template()
     */
    public function newAction()
    {
        $document = new Url();
        $form     = $this->createForm(new UrlType(), $document);

        return array(
            'document' => $document,
            'form'     => $form->createView()
        );
    }

    /**
     * Creates a new Url document.
     *
     * @Route("/create", name="sgkb_admin_urls_create")
     * @Method("post")
     */
    public function createAction()
    {
        $document = new Url();
        $request  = $this->getRequest();
        $form     = $this->createForm(new UrlType(), $document);
        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();
            $dm->persist($document);
            $dm->bindTranslation($document, $this->getDefaultLocale());
            $dm->flush();

            if ($request->isXmlHttpRequest()) {
                return new Response(json_encode(array(
                    'result' => true,
                    'rsp'    => array(
                        'id'    => $document->getId(),
                        'name'  => $document->getName(),
                        'url'   => $document->getUrl(),
                        'slug'  => $document->getSlug()
                    )
                )), 200, array('Content-type' => 'application/json'));
            }

            return $this->redirect($this->generateUrl('sgkb_admin_urls_edit', array('slug' => $document->getSlug())));

        } elseif ($request->isXmlHttpRequest()) {
            $getErrors = function($form, $key = 'global') {
                $errors = array();
                foreach ($form->getErrors() as $error) {
                    $errors[$key] = $error->getMessage();
                }

                return $errors;
            };

            $errors = $getErrors($form);
            foreach ($form->all() as $key => $child) {
                $errors = array_merge($errors, $getErrors($child, $key));
            }

            return new Response(json_encode(array(
                'result' => false,
                'errors' => $errors
            )), 400, array('Content-type' => 'application/json'));
        }

        return $this->render('ServerGroveKbBundle:Admin/Urls:new.html.twig', array(
            'document' => $document,
            'form'     => $form->createView()
        ), new Response('', 400));
    }

    /**
     * Displays a form to edit an existing Url document.
     *
     * @Route("/{slug}/edit", name="sgkb_admin_urls_edit")
     * @Template()
     * @ParamConverter("url", class="ServerGroveKbBundle:Url")
     */
    public function editAction(Url $url, array $forms = array())
    {
        $dm      = $this->getDocumentManager();
        $locales = $this->get('service_container')->getParameter('server_grove_kb.locales');

        $translationForms = array();

        foreach ($locales as $locale) {
            $requiredFields = true;
            try {
                $tmp = $dm->findTranslation('ServerGrove\KbBundle\Document\Url', $url->getId(), $locale, false);
            } catch (\RuntimeException $e) {
                $tmp = clone $url;
                $tmp->setName(null)->setUrl(null);
                $requiredFields = false;
            }

            $form = isset($forms[$locale]) ? $forms[$locale] : $this->createForm(new UrlType($locale, $requiredFields), $tmp);

            $translationForms[$locale] = $form->createView();
        }

        return array(
            'document'          => $url,
            'translation_forms' => $translationForms,
        );
    }

    /**
     * Edits an existing Url document.
     *
     * @Route("/{slug}/update", name="sgkb_admin_urls_update")
     * @Method("post")
     * @ParamConverter("url", class="ServerGroveKbBundle:Url")
     */
    public function updateAction(Url $url)
    {
        $locales          = $this->get('service_container')->getParameter('server_grove_kb.locales');
        $dm               = $this->getDocumentManager();
        $translationForms = array();
        $valid            = true;

        foreach ($locales as $locale) {
            try {
                $translation = $dm->findTranslation('ServerGrove\KbBundle\Document\Url', $url->getId(), $locale, false);
                $required    = true;
            } catch (\RuntimeException $e) {
                $translation = $dm->findTranslation('ServerGrove\KbBundle\Document\Url', $url->getId(), $locale);
                $required = false;
            }
            $form = $this->createForm(new UrlType($locale), $translation);

            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $dm->bindTranslation($translation, $locale);
                $dm->flush();

                $url = $dm->refresh($url);
            } elseif ($required) {
                $valid = false;
            }

            $translationForms[$locale] = $form;
        }

        if ($valid) {
            return $this->redirect($this->generateUrl('sgkb_admin_urls_edit', array('slug' => $url->getSlug())));
        }

        return $this->render('ServerGroveKbBundle:Admin/Urls:edit.html.twig',
            $this->editAction($url, $translationForms),
            new Response('', 400));
    }

    /**
     * Deletes a Url document.
     *
     * @Route("/{slug}/delete", name="sgkb_admin_urls_delete")
     * @Method("post")
     * @ParamConverter("url", class="ServerGroveKbBundle:Url")
     */
    public function deleteAction($url)
    {
        $form    = $this->createDeleteForm($url);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();
            $dm->remove($url);
            $dm->flush();
        }

        return $this->redirect($this->generateUrl('sgkb_admin_urls_index'));
    }

    /**
     * @Template
     * @return array
     */
    public function modalFormAction()
    {
        $urlForm = $this->createForm(new UrlType());

        return array(
            'form' => $urlForm->createView(),
            'wrap' => true
        );
    }

    private function createDeleteForm(Url $url)
    {
        return $this->createFormBuilder(array('slug' => $url->getSlug()))
            ->add('slug', 'hidden')
            ->getForm();
    }
}
