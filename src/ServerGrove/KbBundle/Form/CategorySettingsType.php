<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use ServerGrove\KbBundle\Document\Category;

/**
 * Class CategorySettings
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategorySettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'visibility',
            'choice',
            array(
                'choices' => array(
                    Category::VISIBILITY_PUBLIC  => 'Public',
                    Category::VISIBILITY_PRIVATE => 'Private'
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\Category'));
    }

    public function getName()
    {
        return 'category_settings';
    }
}
