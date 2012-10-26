<?php

namespace ServerGrove\KbBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use ServerGrove\KbBundle\Form\ChoiceList\CategoriesLoader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;

/**
 * Class CategoryType
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoryType extends DoctrineType
{

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     *
     * @return EntityLoaderInterface
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new CategoriesLoader($manager);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sg_category';
    }
}
