<?php

namespace ServerGrove\KbBundle\Form\Type\Prepended;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Class DateType
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class DateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add($builder->getName(), 'date', $options);
    }

    public function getParent()
    {
        return 'date';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = $options['label'];
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sg_prepended_date';
    }
}
