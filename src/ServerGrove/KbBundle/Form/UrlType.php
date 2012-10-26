<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UrlType extends AbstractType
{
    /**
     * @var null|string
     */
    private $locale;

    /**
     * @var bool
     */
    private $requiredFields;

    /**
     * @param string $locale
     * @param bool   $requiredFields
     */
    public function __construct($locale = null, $requiredFields = true)
    {
        $this->locale         = $locale;
        $this->requiredFields = $requiredFields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('required' => $this->requiredFields));
        $builder->add('url', 'url', array('required' => $this->requiredFields));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\Url'));
    }

    public function getName()
    {
        return 'urls'.(is_null($this->locale) ? '' : '_'.$this->locale);
    }
}
