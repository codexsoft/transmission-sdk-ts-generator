<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;

use CodexSoft\Transmission\Schema\Elements\NumberElement;
use JetBrains\PhpStorm\Pure;

/**
 * @property NumberElement $element
 */
class NumberElementTsConverter extends ScalarElementTsConverter
{
    #[Pure]
    public function convert(): string
    {
        $choices = $this->element->getChoicesSourceArray();
        if ($choices) {
            return \implode('|', $choices);
        }

        return 'number';
    }
}
