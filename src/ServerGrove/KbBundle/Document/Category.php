<?php

namespace ServerGrove\KbBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use ServerGrove\KbBundle\Util\Sluggable;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @PHPCRODM\Document(
 *          referenceable=true,
 *          translator="attribute",
 *          repositoryClass="ServerGrove\KbBundle\Repository\CategoryRepository"
 * )
 */
class Category
{
    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PRIVATE = 'private';

    /**
     * @PHPCRODM\Id
     * @var string
     */
    private $id;

    /**
     * @PHPCRODM\ParentDocument
     */
    private $parent = null;

    /**
     * @PHPCRODM\Nodename
     * @var string
     */
    private $slug;

    /**
     * @PHPCRODM\String
     * @var string
     */
    private $path;

    /**
     * @PHPCRODM\Locale
     * @var string
     */
    private $locale;

    /**
     * @PHPCRODM\String(translated=true)
     * @Assert\NotBlank()
     * @var string
     */
    private $name;

    /**
     * @PHPCRODM\String(translated=true)
     * @var string
     */
    private $description;

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

    /**
     * @PHPCRODM\Children()
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $children;

    /**
     * @PHPCRODM\Referrers(referenceType="hard")
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $articles;

    /**
     * @PHPCRODM\String
     * @var string
     */
    private $visibility;

    private $displayName;

    /**
     *
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->setVisibility(self::VISIBILITY_PUBLIC);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->displayName instanceof \Closure) {
            return call_user_func($this->displayName, $this);
        }

        return $this->getName();
    }

    /**
     * @param  callable $displayName
     * @return Category
     */
    public function setDisplayName(\Closure $displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Category $parent
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Category
     */
    public function getTopCategory()
    {
        $category = $this;
        while ($category->getParent() instanceof self) {
            $category = $category->getParent();
        }

        return $category;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @PHPCRODM\PrePersist
     */
    public function setPath($path = null)
    {
        if (is_null($path)) {
            if ($this->getParent() instanceof static) {
                $path = $this->getParent()->getPath().'/'.$this->getSlug();
            } else {
                $path = $this->getSlug();
            }
            $this->path = $path;
        } else {
            $this->path = $path;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $locale
     *
     * @return \ServerGrove\KbBundle\Document\Category
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
     * @param $name
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function setName($name)
    {
        $this->name = $name;
        if (is_null($this->slug)) {
            $this->slug = Sluggable::urlize($name);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return is_string($this->name) ? $this->name : '';
    }

    /**
     * @param $description
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @PHPCRODM\PrePersist
     */
    public function setCreated()
    {
        $this->created = new \DateTime();

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
     * @PHPCRODM\PreUpdate
     */
    public function setUpdated($value = null)
    {
        if (!$value) {
            $value = new \DateTime();
        }
        $this->updated = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Article $article
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function addArticle(Article $article)
    {
        $this->articles->add($article);

        return $this;
    }

    /**
     * @param Article $article
     *
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function removeArticle(Article $article)
    {
        $this->articles->removeElement($article);

        return $this;
    }

    /**
     * @param  bool                                         $sorted
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles($sorted = false)
    {
        if ($sorted) {
            $collection = new ArrayCollection();
            $articles   = $this->articles->toArray();
            usort(
                $articles,
                function (Article $articleA, Article $articleB) {
                    if ($articleA->getTitle() == $articleB->getTitle()) {
                        return 0;
                    }

                    return strnatcmp($articleA->getTitle(), $articleB->getTitle());
                }
            );

            foreach ($articles as $article) {
                $collection->add($article);
            }

            return $collection;
        }

        return $this->articles;
    }

    /**
     * Sets the value of visibility
     *
     * @param  string                                  $visibility
     * @return \ServerGrove\KbBundle\Document\Category
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Returns the value of visibility
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
}
