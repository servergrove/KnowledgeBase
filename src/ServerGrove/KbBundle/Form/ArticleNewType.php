<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class ArticleNewType extends ArticleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title');
        parent::buildForm($builder, $options);
    }
}
