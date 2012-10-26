<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ServerGrove\KbBundle\Document\ArticleFile;
use ServerGrove\KbBundle\Form\ArticleFileType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ArticlesFilesController
 *
 * @Route("/admin/{_locale}/articles/files", requirements={"_locale"="en|es|pt"})
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticlesFilesController extends Controller
{
    /**
     * @param string $_format
     *
     * @return array
     *
     * @Route("/all.{_format}", name="sgkb_admin_articles_files_all", requirements={"_format"="html|json"})
     * @method("get")
     */
    public function allAction($_format)
    {
        $repo = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:ArticleFile');
        $all  = array();

        /** @var $file ArticleFile */
        foreach ($repo->findAll() as $file) {
            $all[$file->getId()] = array('path' => $file->getPath());
        }

        if ('json' === $_format) {
            $out = json_encode($all);
        } else {
            $out = var_export($all, true);
        }

        return new Response($out);
    }

    /**
     * @return array
     *
     * @Route("/uploader", name="sgkb_admin_articles_files_uploader")
     * @method("get")
     * @Template()
     */
    public function uploaderAction()
    {
        $form = $this->createForm(new ArticleFileType(), new ArticleFile());

        return array('form' => $form->createView());
    }

    /**
     * @Route("/upload", name="sgkb_admin_articles_files_upload")
     * @method("post")
     * @return array
     */
    public function uploadAction()
    {
        $form    = $this->createForm(new ArticleFileType(), $document = new ArticleFile());
        $status  = 400;
        $refresh = false;

        $form->bind($this->getRequest());
        if ($form->isValid()) {
            /** @var $data \Symfony\Component\HttpFoundation\File\UploadedFile */
            $data = $form->get('path')->getData();
            $file = $data->move($this->get('kernel')->getRootDir().'/../web/uploads', date('YmdHi-').$data->getClientOriginalName());

            $document->setPath('/uploads/'.$file->getBasename());

            $dm = $this->getDocumentManager();

            $dm->persist($document);
            $dm->flush();

            $status  = 200;
            $form    = $this->createForm(new ArticleFileType(), new ArticleFile());
            $refresh = true;
        }

        return $this->render('ServerGroveKbBundle:Admin/ArticlesFiles:uploader.html.twig', array(
            'form'    => $form->createView(),
            'refresh' => $refresh
        ), new Response('', $status));
    }
}
