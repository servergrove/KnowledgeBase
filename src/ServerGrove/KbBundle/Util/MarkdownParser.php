<?php

namespace ServerGrove\KbBundle\Util;

use Knp\Bundle\MarkdownBundle\Parser\MarkdownParser as BaseParser;

/**
 * Class MarkdownParser
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class MarkdownParser extends BaseParser
{
    public function doFencedCodeBlocks($text)
    {
        $text = preg_replace_callback('{
                (?:\n|\A)
                # 1: Opening marker
                (
                    `{3,} # Marker: three tilde or more.
                )
                [ ]* \n # Whitespace and newline following marker.

                # 2: Content
                (
                    (?>
                        (?!\1 [ ]* \n)	# Not a closing marker.
                        .*\n+
                    )+
                )

                # Closing marker.
                \1 [ ]* \n
            }xm',
            array(&$this, '_doFencedCodeBlocks_callback'), $text);

        return parent::doFencedCodeBlocks($text);
    }
}
