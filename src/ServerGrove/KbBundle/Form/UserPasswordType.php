<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserPasswordType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', 'repeated', array(
                'type'            => 'password',
                'invalid_message' => 'Password fields must match.',
                'first_name'      => 'password',
                'second_name'     => 'confirm_password'
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\User'));
    }

    public function getName()
    {
        return 'user_password';
    }
}
