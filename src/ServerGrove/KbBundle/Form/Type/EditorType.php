<?php

namespace ServerGrove\KbBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class EditorType
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class EditorType extends AbstractType
{

    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['type'] = $options['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('type' => $this->type));
        $resolver->setAllowedValues(array('type' => array('markdown', 'wysiwyg')));
    }

    public function getParent()
    {
        return 'textarea';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sg_editor';
    }
}
