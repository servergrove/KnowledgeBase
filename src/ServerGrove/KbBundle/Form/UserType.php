<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('attr' => array('autocomplete'=> 'off')))
            ->add('email', null, array('attr' => array('autocomplete'=> 'off')))
            ->add('username', null, array('attr' => array('autocomplete'=> 'off')))
            ->add('password', 'repeated', array(
                'type'            => 'password',
                'invalid_message' => 'Password fields must match.',
                'first_name'      => 'password',
                'second_name'     => 'confirm_password'
            ))
            ->add('roles', 'choice', array(
                'choices'  => array(
                    'ROLE_ADMIN'       => 'Admin',
                    'ROLE_EDITOR'      => 'Editor',
                    'ROLE_CONTRIBUTOR' => 'Contributor'
                ),
                'multiple' => true
            ))
            ->add('enabled', null, array('label' => 'Is Active', 'required' => false))
            ->add('locked', 'hidden')
            ->add('expirationDate', 'sg_prepended_date', array(
                'years' => range(date('Y'), date('Y') + 5),
                'label' => 'Expiration Date',
                'attr'  => array('disabled' => 'disabled')
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\User'));
    }

    public function getName()
    {
        return 'user';
    }
}
