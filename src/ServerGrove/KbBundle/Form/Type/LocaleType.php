<?php

namespace ServerGrove\KbBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\LocaleType as BaseType;
use Symfony\Component\Locale\Locale;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleType extends BaseType
{

    private $locales;

    public function __construct(array $locales)
    {
        $this->locales = array_combine($locales, $locales);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array_intersect_key(Locale::getDisplayLocales(\Locale::getDefault()), $this->locales);
        $resolver->setDefaults(array('choices' => $choices));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sg_locale';
    }
}
