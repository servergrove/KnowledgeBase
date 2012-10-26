<?php

namespace ServerGrove\KbBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;
use ServerGrove\KbBundle\Util\Sluggable;

/**
 * ServerGrove\KbBundle\Document\Url
 *
 * @PHPCRODM\Document(referenceable=true, translator="attribute", repositoryClass="ServerGrove\KbBundle\Repository\UrlRepository")
 */
class Url
{
    /**
     * @var string
     * @PHPCRODM\Id(strategy="repository")
     */
    private $id;

    /**
     * @var string
     * @PHPCRODM\String(translated=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @PHPCRODM\Uri(translated=true)
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $url;

    /**
     * @var string
     * @PHPCRODM\String
     */
    private $slug;

    /**
     * @var string
     * @PHPCRODM\Locale
     */
    private $locale;

    /**
     * @var \DateTime
     * @PHPCRODM\Date
     */
    private $created_at;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string                             $id
     * @return \ServerGrove\KbBundle\Document\Url
     */
    protected function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param  string                             $name
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function setName($name)
    {
        $this->name = $name;

        if (is_null($this->slug)) {
            $this->setSlug(Sluggable::urlize($this->name));
        }

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return is_string($this->name) ? $this->name : '';
    }

    /**
     * Sets the created date
     *
     * @PHPCRODM\PrePersist
     *
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function registerCreatedDate()
    {
        $this->created_at = new \DateTime();

        return $this;
    }

    /**
     * Set url
     *
     * @param  string                             $url
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $locale
     *
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set created_at
     *
     * @param  \DateTime                          $createdAt
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param  string                             $slug
     * @return \ServerGrove\KbBundle\Document\Url
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
