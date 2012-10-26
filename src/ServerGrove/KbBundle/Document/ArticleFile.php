<?php

namespace ServerGrove\KbBundle\Document;

namespace ServerGrove\KbBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * Class ArticleFile
 *
 * @PHPCRODM\Document(
 *      repositoryClass="ServerGrove\KbBundle\Repository\ArticleFileRepository"
 * )
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticleFile
{

    /**
     * @var string
     * @PHPCRODM\Id(strategy="repository")
     */
    private $id;

    /**
     * @var string
     * @PHPCRODM\String
     */
    private $path;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
