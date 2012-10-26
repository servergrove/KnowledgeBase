<?php

namespace ServerGrove\KbBundle\Repository;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Id\RepositoryIdInterface;

/**
 * Class UrlRepository
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UrlRepository extends DocumentRepository implements RepositoryIdInterface
{

    /**
     * Generate a document id
     *
     * @param \ServerGrove\KbBundle\Document\Url $document
     * @param object                             $parent
     *
     * @return string
     */
    public function generateId($document, $parent = null)
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $this->getDocumentManager()->getPhpcrSession();
        $root    = $session->getNode('/');

        if (!$root->hasNode('url')) {
            $root->addNode('url');
        }

        return '/url/'.$document->getSlug();
    }
}
