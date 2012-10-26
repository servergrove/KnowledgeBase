<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('categories', 'sg_category', array(
            'multiple'      => true,
            'expanded'      => false,
            'class'         => 'ServerGrove\KbBundle\Document\Category',
            'query_builder' => '',
            'attr'          => array('size' => 11, 'class' => 'input-xxlarge')
        ));

        if ($options['enable_related_urls']) {
            $builder->add('urls', 'phpcr_document', array(
                'class'    => 'ServerGrove\KbBundle\Document\Url',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr'     => array('class' => 'url-selector')
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'          => 'ServerGrove\KbBundle\Document\Article',
            'enable_related_urls' => false,
            'loader'              => null
        ));
    }

    public function getName()
    {
        return 'article';
    }
}
