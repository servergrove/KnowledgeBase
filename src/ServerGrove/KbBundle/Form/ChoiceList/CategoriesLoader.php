<?php

namespace ServerGrove\KbBundle\Form\ChoiceList;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use ServerGrove\KbBundle\Document\Category;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

/**
 * Class CategoriesLoader
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoriesLoader implements EntityLoaderInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return array The entities.
     */
    public function getEntities()
    {
        /** @var $categories \Doctrine\Common\Collections\ArrayCollection */
        $parents    = $this->manager->getRepository('ServerGroveKbBundle:Category')->findAllParents();
        $categories = new ArrayCollection();

        foreach ($parents as $parent) {
            $this->addCategoryToArrayCollection($categories, $parent);
        }

        return $categories;
    }

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array $values The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        /** @var $entities ArrayCollection */
        $entities = $this->getEntities();

        return $entities->filter(function($document) use ($values) {
            return method_exists($document, 'getId') && in_array($document->getId(), $values);
        })->toArray();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $categories
     * @param \ServerGrove\KbBundle\Document\Category      $category
     * @param int                                          $spaces
     */
    public function addCategoryToArrayCollection(ArrayCollection $categories, Category $category, $spaces = 0)
    {
        $categories->add($category);
        $category->setDisplayName(function(Category $category) use ($spaces) {
            return str_repeat('- ', $spaces).$category->getName();
        });
        foreach ($category->getChildren() as $child) {
            call_user_func_array(array($this, __FUNCTION__), array($categories, $child, $spaces + 1));
        }
    }
}
