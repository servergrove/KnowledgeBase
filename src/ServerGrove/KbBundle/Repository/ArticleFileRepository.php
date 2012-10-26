<?php

namespace ServerGrove\KbBundle\Repository;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Id\RepositoryIdInterface;
use ServerGrove\KbBundle\Util\Sluggable;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticleFileRepository extends DocumentRepository implements RepositoryIdInterface
{

    /**
     * Generate a document id
     *
     * @param \ServerGrove\KbBundle\Document\ArticleFile $document
     * @param object                                     $parent
     *
     * @return string
     */
    public function generateId($document, $parent = null)
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $this->getDocumentManager()->getPhpcrSession();
        $root    = $session->getNode('/');

        if (!$root->hasNode('articles-files')) {
            $root->addNode('articles-files');
        }

        return '/articles-files/'.Sluggable::urlize($document->getPath());
    }
}
