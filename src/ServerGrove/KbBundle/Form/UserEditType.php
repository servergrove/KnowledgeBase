<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UserEditType
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UserEditType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('password');
    }
}
