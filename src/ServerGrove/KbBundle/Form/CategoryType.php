<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CategoryType
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CategoryType extends AbstractType
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale = '')
    {
        $this->locale = $locale;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('locale', 'hidden', array('required' => !empty($this->locale)));
        $builder->add('name');
        $builder->add('description', 'textarea', array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\Category', 'id_prefix' => ''));
    }

    public function getName()
    {
        return empty($this->locale) ? 'category' : 'category_translation_'.$this->locale;
    }
}
