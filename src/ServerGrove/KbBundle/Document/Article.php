<?php

namespace ServerGrove\KbBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;
use ServerGrove\KbBundle\Util\Sluggable;

/**
 * @PHPCRODM\Document(
 *      versionable="full",
 *      referenceable=true,
 *      translator="attribute",
 *      repositoryClass="ServerGrove\KbBundle\Repository\ArticleRepository"
 * )
 */
class Article
{
    const CONTENT_TYPE_MARKDOWN = 'markdown';

    const CONTENT_TYPE_WYSIWYG = 'wysiwyg';

    /**
     * @var string
     * @PHPCRODM\Id(strategy="repository")
     */
    private $id;

    /**
     * @var string
     * @PHPCRODM\String
     */
    private $slug;

    /**
     * @var boolean
     * @PHPCRODM\Boolean(translated=true)
     * @Assert\Type(type="bool")
     */
    private $isActive = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @PHPCRODM\ReferenceMany(targetDocument="Category", strategy="hard")
     */
    private $categories;

    /**
     * @var string
     * @PHPCRODM\Locale
     */
    private $locale;

    /**
     * @var string
     * @PHPCRODM\String(translated=true)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     * @PHPCRODM\String(translated=true)
     */
    private $content;

    /**
     * @var string
     * @PHPCRODM\String(translated=true)
     */
    private $contentType;

    /**
     * @var string
     * @PHPCRODM\VersionName
     */
    private $versionName;

    /**
     * @PHPCRODM\VersionCreated
     */
    private $versionCreated;

    /**
     * @var int
     * @PHPCRODM\Int
     */
    private $views;

    /**
     * @var array
     * @PHPCRODM\String(multivalue=true)
     */
    private $keywords;

    /**
     * @var array
     * @PHPCRODM\String(multivalue=true)
     */
    private $metadata;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @PHPCRODM\ReferenceMany(targetDocument="User")
     */
    private $subscriptors;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @PHPCRODM\ReferenceMany(targetDocument="Url")
     */
    private $urls;

    /**
     * @PHPCRODM\Date
     * @var \DateTime
     */
    private $created;

    /**
     * @PHPCRODM\Date
     * @var \DateTime
     */
    private $updated;

    public function __construct()
    {
        $this->categories   = new ArrayCollection();
        $this->subscriptors = new ArrayCollection();
        $this->urls         = new ArrayCollection();
        $this->metadata     = array();
        $this->keywords     = array();
        $this->contentType  = self::CONTENT_TYPE_MARKDOWN;
        $this->isActive     = false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $title
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        if (is_null($this->slug)) {
            $this->setSlug(Sluggable::urlize($title));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return is_string($this->title) ? $this->title : '';
    }

    /**
     * @PHPCRODM\PrePersist
     */
    public function registerCreated()
    {
        $this->setCreated(new \DateTime());

        return $this;
    }

    /**
     * @param $created
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @PHPCRODM\PreUpdate
     */
    public function registerUpdated()
    {
        $this->setUpdated(new \DateTime());

        return $this;
    }

    /**
     * @param $updated
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $value
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setIsActive($value)
    {
        $this->isActive = $value;

        return $this;
    }

    /**
     * @param Category $category
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function addCategory(Category $category)
    {
        $category->addArticle($this);
        $this->categories->add($category);

        return $this;
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category->removeArticle($this));

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return Category
     */
    public function getDefaultCategory()
    {
        return $this->getCategories()->first();
    }

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

    /**
     * @param int $views
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Increases the number of views registered for this article
     */
    public function registerView()
    {
        if (is_null($this->views)) {
            $this->views = 0;
        }
        $this->views++;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubscriptors()
    {
        return $this->subscriptors;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setMetadata($key, $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function getMetadata($key, $default = null)
    {
        if ($this->hasMetadata($key)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasMetadata($key)
    {
        return isset($this->metadata[$key]);
    }

    /**
     * @return array
     */
    public function getAllMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string|array $keywords
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setKeywords($keywords)
    {
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        if (is_array($keywords)) {
            $this->keywords = array_unique($keywords);
        }

        return $this;
    }

    /**
     * @return \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keyword
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function addKeyword($keyword)
    {
        $this->keywords[] = $keyword;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @param \ServerGrove\KbBundle\Document\Url $url
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function addUrl(Url $url)
    {
        $this->urls->add($url);

        return $this;
    }

    public function removeUrl(Url $url)
    {
        $this->urls->removeElement($url);

        return $this;
    }

    /**
     * @param $content
     *
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $locale
     *
     * @return \ServerGrove\KbBundle\Document\Article
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
     * @param  string                                 $contentType
     * @return \ServerGrove\KbBundle\Document\Article
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
