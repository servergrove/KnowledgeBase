<?php

namespace ServerGrove\KbBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class TextExtension extends \Twig_Extension
{

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            'booltostr' => new \Twig_Filter_Method($this, 'booltostr')
        );
    }

    public function booltostr($bool)
    {
        $bool = (bool) $bool;

        return $this->translator->trans($bool ? 'Yes' : 'No');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sgkb_text';
    }
}
