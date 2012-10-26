<?php

namespace ServerGrove\KbBundle\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use ServerGrove\KbBundle\Document\Category;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadCategoriesData
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LoadCategoriesData implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $session = $manager->getPhpcrSession();
        $session->getRootNode()->addNode('categories');

        /** @var $parent \Doctrine\ODM\PHPCR\Document\Generic */
        $parent = $manager->find(null, '/categories');

        $this->addCategory($manager, 'Homepage', 'Category for homepage articles', $parent, true);
        $this->addCategory($manager, 'Category A', 'Description', $parent);
        $this->addCategory($manager, 'Category B', 'Description', $parent);
        $this->addCategory($manager, 'Category C', 'Description', $parent);

        $category = $this->addCategory($manager, 'Test', 'This is the description of the test category', $parent);
        $this->addCategory($manager, 'Child', 'Description of child category', $category);

/*
        $category = $this->addCategory($manager, 'CategoryD', 'This is the description of the test category', $parent);
        $this->addCategory($manager, 'Child', 'Description of child category', $category);
  */      $manager->flush();

        $this->addTranslation($manager, $category, 'es', 'Prueba', 'Esta es la descripción de la categoría de prueba');
    }

    /**
     * @param  \Doctrine\Common\Persistence\ObjectManager                                   $manager
     * @param  string                                                                       $name
     * @param  string                                                                       $description
     * @param  \ServerGrove\KbBundle\Document\Category|\Doctrine\ODM\PHPCR\Document\Generic $parent
     * @param  bool                                                                         $private
     * @return \ServerGrove\KbBundle\Document\Category
     */
    private function addCategory(ObjectManager $manager, $name, $description, $parent, $private = false)
    {

        $category = new Category();
        $category->setParent($parent);
        $category->setName($name);
        $category->setDescription($description);
        $category->setVisibility($private ? Category::VISIBILITY_PRIVATE : Category::VISIBILITY_PUBLIC);

        $manager->persist($category);
        $manager->bindTranslation($category, 'en');
        $manager->flush($category);

        return $category;
    }

    private function addTranslation(ObjectManager $manager, Category $category, $locale, $name, $description)
    {
        $category->setName($name);
        $category->setDescription($description);

        $manager->bindTranslation($category, $locale);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }

}
