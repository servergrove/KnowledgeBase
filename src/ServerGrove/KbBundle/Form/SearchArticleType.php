<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use ServerGrove\KbBundle\Repository\CategoryRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchArticleType extends AbstractType
{
    /**
     * @var \ServerGrove\KbBundle\Repository\CategoryRepository
     */
    private $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();

        foreach ($this->repository->findAllParentsActive() as $category) {
            $choices[$category->getId()] = $category->__toString();
        }

        $builder->add(
            'category',
            'choice',
            array(
                'required'    => false,
                'expanded'    => false,
                'multiple'    => false,
                'choices'     => $choices,
                'empty_value' => 'Search all',
            )
        );
        $builder->add('keywords', 'search');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('csrf_protection' => false));
    }

    public function getName()
    {
        return 'article';
    }
}
