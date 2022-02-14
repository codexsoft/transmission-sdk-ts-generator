<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;

use CodexSoft\Transmission\Schema\Elements\StringElement;

/**
 * @property StringElement $element
 */
class StringElementTsConverter extends ScalarElementTsConverter
{
    public function convert(): string
    {
        $choices = $this->element->getChoicesSourceArray();
        if ($choices) {
            return \implode('|', \array_map(static fn ($choice) => "'".$choice."'", $choices));
        }

        return 'string';
    }
}
