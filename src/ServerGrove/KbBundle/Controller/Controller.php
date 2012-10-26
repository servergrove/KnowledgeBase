<?php

namespace ServerGrove\KbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

/**
 * Class Controller
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class Controller extends BaseController
{

    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $documentManager;

    /**
     * @return \Doctrine\ODM\PHPCR\DocumentManager
     */
    public function getDocumentManager()
    {
        if (!$this->documentManager) {
            $this->documentManager = $this->get('doctrine_phpcr.odm.document_manager');
        }

        return $this->documentManager;
    }

    /**
     * @return \ServerGrove\KbBundle\Repository\ArticleRepository
     */
    protected function getArticleRepository()
    {
        return $this->getDocumentManager()->getRepository("ServerGroveKbBundle:Article");
    }

    /**
     * @return \ServerGrove\KbBundle\Repository\CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->getDocumentManager()->getRepository("ServerGroveKbBundle:Category");
    }
}
